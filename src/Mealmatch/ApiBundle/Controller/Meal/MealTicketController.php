<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Controller\Meal;

use Doctrine\ORM\EntityManager;
use Dompdf\Dompdf;
use Dompdf\Options;
use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\Entity\Coupon\RedeemRequest;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\ApiBundle\Entity\Meal\MealJoinRequest;
use Mealmatch\ApiBundle\Entity\Meal\MealOffer;
use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\Exceptions\MealTicketException;
use Mealmatch\ApiBundle\MealMatch\FlashTypes;
use Mealmatch\ApiBundle\MealMatch\Traits\Referer;
use Mealmatch\ApiBundle\Services\MealTicketService;
use Mealmatch\CouponBundle\Coupon\CouponInterface;
use MMApiBundle\Entity\MealTicket;
use MMUserBundle\Entity\MMUser;
use MMUserBundle\Entity\MMUserProfile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class MealTicketController does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 *
 * @Route("api/mealticket")
 * @Security("has_role('ROLE_USER')")
 */
class MealTicketController extends ApiController
{
    use Referer;

    /**
     * @param Request         $request
     * @param MealJoinRequest $joinRequest
     * @Route("/{id}/create", name="api_mealticket_create_from_joinreq")
     *
     * @throws MealTicketException
     *
     * @return RedirectResponse
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     */
    public function createTicketFromJoinRequestAction(Request $request, MealJoinRequest $joinRequest): RedirectResponse
    {
        $extraGuests = $joinRequest->getExtraGuest();
        $meal = $joinRequest->getBaseMeal();
        $guest = $joinRequest->getCreatedBy();

        /** @var MealTicketService $mealTicketService */
        $mealTicketService = $this->get('api.meal_ticket.service');

        // $mealTicket = $mealTicketService->findOrCreateNew($meal, $guest, $extraGuests);
        if ($mealTicketService->hasHomeMealTicket($joinRequest)) {
            $mealTicket = $mealTicketService->restoreFromJoinRequest($joinRequest);
            $this->get('logger')->addInfo(sprintf('Guest has Mealticket. RESTORING: '));
        } else {
            $mealTicket = $mealTicketService->createNewFromHomeMeal($meal, $guest, $extraGuests);
            $this->get('logger')->addInfo(sprintf('Guest has NO Mealticket. Creating a NEW one: '));
        }

        return $this->redirectToRoute('api_mealticket_show', array('id' => $mealTicket->getId()));
    }

    /**
     * Creates a new MealTicket for a ProMeal.
     * If no MealOffer is choosen, the first MealOffer ist used.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Request        $request
     * @param ProMeal        $proMeal
     * @param MealOffer|null $mealOffer
     *
     * @return RedirectResponse
     *
     * @Route("/{id}/{mealOffer}/createTicket", name="api_mealticket_create_from_promeal",
     *  defaults={"id"="","mealOffer"=""}
     * )
     */
    public function createMealTicketFromProMeal(Request $request, ProMeal $proMeal, MealOffer $mealOffer = null): RedirectResponse
    {
        // Initialize helper
        $this->init();
        // Select first mealOffer if mealOffer is NULL.
        $this->selectFirstOfferIfNull($proMeal, $mealOffer);

        // What if the choosen offer doesnt match the specified meal?
        $proMealOffers = $proMeal->getMealOffers();
        $offerMatches = false;
        /** @var MealOffer $offer */
        foreach ($proMealOffers as $offer) {
            if ($offer->getId() === $mealOffer->getId()) {
                // found it!
                $offerMatches = true;
            }
        }
        // it DOES NOT MATCH (!) - Send user back to specified meal to select again :)
        if (!$offerMatches) {
            $this->logger->addWarning('Selected MealOffer does not match specified ProMeal!');
            $this->logger->addWarning(
                sprintf('Redirect to ProMeal(%s)', $proMeal->getId()
                )
            );
            // Redirect to the specified promeal
            return $this->redirectToRoute('public_promeal_show', array('id' => $proMeal->getId()));
        }
        // (!) The MealOffer matches the selected ProMeal (!)

        // Use the current user (guest), who clicked on "pay to join meal" to create the MealTicket.
        /** @var MMUser $guest */
        $guest = $this->get('security.token_storage')->getToken()->getUser();

        // Use the MealticketService for Mealmatch specific business logic.
        /** @var MealTicketService $mealTicketService */
        $mealTicketService = $this->get('api.meal_ticket.service');

        /** @var BaseMealTicket $mealTicket */
        $mealTicket = null;

        // Check if we did already create a ticket for that meal and guest.
        if ($mealTicketService->hasProMealTicket($guest, $proMeal, $mealOffer)) {
            // We already created a ticket, use that one.
            $mealTicket = $mealTicketService->getProMealTicket($guest, $proMeal, $mealOffer);
            $this->logger->addDebug(sprintf('Show already existing Ticket(%s)!', $mealTicket->getId()));
        } else {
            // Create a new MealTicket
            try {
                $mealTicket = $mealTicketService->createNewFromProMeal($proMeal, $mealOffer, $guest);
            } catch (MealTicketException $mealTicketException) {
                $this->logger->addError('Failed to create MealTicket!', $mealTicketException->getMessage());
                // Redirect to contact, with a toaster msg.
                $tosterMsg = $this->trans('mealticket.error.response.toaster.msg');
                $this->addFlash(FlashTypes::$DANGER, $tosterMsg);

                return $this->redirectToRoute('contact_show_form');
            }
            $this->logger->addDebug(sprintf('Show already existing Ticket(%s)!', $mealTicket->getId()));
        }

        return $this->redirectToRoute('api_mealticket_show', array('id' => $mealTicket->getId()));
    }

    /**
     * @param Request        $request
     * @param BaseMealTicket $mealTicket
     * @Route("/{id}/show", name="api_mealticket_show")
     *
     * @return Response
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     */
    public function showAction(Request $request, BaseMealTicket $mealTicket)
    {
        $renderViewData = $this->createMealTicketViewData($request, $mealTicket);
        $response = $this->render('@WEBUI/MealTicket/show.html.twig', $renderViewData);
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('max-age', 0);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('no-store', true);

        return $response;
    }

    /**
     * @param Request        $request
     * @param BaseMealTicket $mealTicket
     * @Route("/{id}/update", name="api_mealticket_update_payment_options")
     *
     * @return RedirectResponse
     */
    public function updatePaymentOption(Request $request, BaseMealTicket $mealTicket): RedirectResponse
    {
        $updateForm = $this->createForm('Mealmatch\ApiBundle\Form\MealTicket\PaymentOptionsType', $mealTicket);
        $updateForm->handleRequest($request);

        if ($updateForm->isSubmitted() && $updateForm->isValid()) {
            $this->getDoctrine()->getManager()->persist($mealTicket);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute(
            'api_mealticket_show',
            array(
                'id' => $mealTicket->getId(),
            )
        );
    }

    /**
     * @param Request        $request
     * @param BaseMealTicket $mealTicket
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Mealmatch\MangopayBundle\Exceptions\MangopayApiException
     *
     * @return RedirectResponse
     * @Route("/{mealTicket}/preparePayin", name="api_mealticket_prepare_payin", methods={"POST"})
     */
    public function preparePayin(Request $request, BaseMealTicket $mealTicket): RedirectResponse
    {
        $updateForm = $this->createForm('Mealmatch\ApiBundle\Form\MealTicket\PayinRequiredDataType');
        $updateForm->handleRequest($request);
        if ($updateForm->isSubmitted() && $updateForm->isValid()) {
            /** @var MMUserProfile $guestProfile */
            $guestProfile = $mealTicket->getGuest()->getProfile();
            $guestProfile->setFirstName($updateForm->getData()['firstName']);
            $guestProfile->setLastName($updateForm->getData()['lastName']);
            $guestProfile->setAddressLine1($updateForm->getData()['address']);
            $guestProfile->setAreaCode($updateForm->getData()['postalCode']);
            $guestProfile->setCity($updateForm->getData()['city']);
            $guestProfile->setState($updateForm->getData()['region']);
            $guestProfile->setBirthday($updateForm->getData()['birthday']);
            $guestProfile->setNationality($updateForm->getData()['nationality']);
            $guestProfile->setCountry($updateForm->getData()['countryOfResidence']);

            $mangopayService = $this->get('PublicMangopayService');

            /** @var MMUser $guest */
            $guest = $mealTicket->getGuest();
            $userNatural = $mangopayService->getMangopayUserService()->createUserNaturalFrom($guest);
            $userNaturalID = $mangopayService->getMangopayUserService()->doCreateUserNatural($userNatural);
            $createdUserNatural = $mangopayService->getMangopayUserService()->getUser($userNaturalID);
            $createdWallet = $mangopayService->getMangopayWalletService()->doCreateWallet($createdUserNatural);

            $guestPaymentProfile = $guest->getPaymentProfile();
            $guestPaymentProfile->setMangopayID($userNaturalID);
            $guestPaymentProfile->setMangopayWalletID($createdWallet->Id);

            // Now Payin should be possible, change payable to true
            $mealTicket->setPayable(true);

            /** @var EntityManager $entityManager */
            $entityManager = $this->get('doctrine.orm.default_entity_manager');
            $entityManager->persist($guestProfile);
            $entityManager->persist($mealTicket);
            $entityManager->flush();
        }

        return $this->redirectToRoute(
            'api_mealticket_show',
            array(
                'id' => $mealTicket->getId(),
            )
        );
    }

    /**
     * @param Request        $request
     * @param BaseMealTicket $mealTicket
     *
     * @return RedirectResponse
     * @Route("/{mealTicket}/redeemCode", name="api_mealticket_redeem_code", methods={"POST"})
     */
    public function redeemCode(Request $request, BaseMealTicket $mealTicket): RedirectResponse
    {
        /**
         * @var RedeemRequest
         */
        $newRReq = new RedeemRequest();
        $newRReq->setMealTicket($mealTicket);

        $updateForm = $this->createForm('Mealmatch\CouponBundle\Form\RedeemRequestType', $newRReq);
        $updateForm->handleRequest($request);

        if ($updateForm->isSubmitted() && $updateForm->isValid()) {
            $this->getDoctrine()->getManager()->persist($mealTicket);
            $this->getDoctrine()->getManager()->persist($newRReq);
            $this->getDoctrine()->getManager()->flush();
        }

        $result = $this->get('Mealmatch\CouponBundle\Services\PublicCouponService')
            ->redeem(
                $mealTicket,
                $newRReq->getCodeString()
            );

        if (\array_key_exists('ERROR', $result)) {
            $this->addFlash(FlashTypes::$DANGER, $result['ERROR']);
        } else {
            /** @var CouponInterface $coupon */
            $coupon = $result['Coupon'];
            $this->addFlash(FlashTypes::$SUCCESS, 'Coupon with code: '.$coupon->getCouponCode().' applied!');
        }

        return $this->redirectToRoute(
            'api_mealticket_show',
            array(
                'id' => $mealTicket->getId(),
            )
        );
    }

    /**
     * @param Request        $request
     * @param BaseMealTicket $mealTicket
     * @Route("/{id}/processRedeemRequest", name="api_mealticket_process_redeem_request")
     *
     * @return RedirectResponse
     */
    public function processRedeemRequest(Request $request, BaseMealTicket $mealTicket, RedeemRequest $redeemRequest)
    {
        $updateForm = $this->createForm('Mealmatch\CouponBundle\Form\RedeemRequestType', $redeemRequest);
        $updateForm->handleRequest($request);

        if ($updateForm->isSubmitted() && $updateForm->isValid()) {
            $mealTicket->addRedeemRequest($redeemRequest);
            $this->getDoctrine()->getManager()->persist($mealTicket);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute(
            'api_mealticket_show',
            array(
                'id' => $mealTicket->getId(),
            )
        );
    }

    /**
     * @Route("/{id}/print", name="api_mealticket_print")
     *
     * @param Request        $request
     * @param BaseMealTicket $mealTicket
     *
     * @return BinaryFileResponse
     */
    public function printMealTicket(Request $request, BaseMealTicket $mealTicket): BinaryFileResponse
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Helvetica');
        $pdfOptions->setIsRemoteEnabled(true);
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        $renderViewData = $this->createMealTicketViewData($request, $mealTicket);
        $renderViewData['print'] = true;
        $renderViewData['webDir'] = $this->getParameter('kernel.project_dir').'/web';
        $html = $this->renderView('@WEBUI/MealTicket/print.html.twig', $renderViewData);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        $tmpDir = $this->getParameter('kernel.cache_dir').'/';
        if (!is_dir($tmpDir)) {
            if (!mkdir($tmpDir) && !is_dir($tmpDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $tmpDir));
            }
        }

        $domOptions = new Options();
        $domOptions->setTempDir($tmpDir);
        $rootDir = $this->getParameter('kernel.cache_dir');
        $domOptions->setRootDir($rootDir);
        $dompdf->setOptions($domOptions);

        $mealTicketFileName = $mealTicket->getNumber().'.pdf';
        $fileData = $dompdf->output();
        $fPointer = fopen($tmpDir.$mealTicketFileName, 'w');
        file_put_contents($tmpDir.$mealTicketFileName, $fileData);

        return new BinaryFileResponse($tmpDir.$mealTicketFileName);
//        $this->get('api.aws_uploader')->uploadByFileData($fileData, $mealTicketFileName);
//
//        return $this->redirect(
//            'https://mealmatch-stage.objects.frb.io/'.$mealTicketFileName
//        );
//
////        return $this->redirectToRoute(
//            'api_mealticket_show',
//            array(
//                'id' => $mealTicket->getId(),
//            )
//        );
    }

    private function createMealTicketViewData(Request $request, BaseMealTicket $mealTicket): array
    {
        $viewData = array(
            'title' => 'Dein Mealmatch Ticket',
        );

        $mealTicketForm = $this->createForm(
            'Mealmatch\ApiBundle\Form\MealTicket\PaymentOptionsType',
            $mealTicket,
            array(
                'action' => $this->generateUrl(
                    'api_mealticket_update_payment_options',
                    array('id' => $mealTicket->getId())
                ),
                'method' => 'POST',
            )
        );

        // The redeemRequest comes from the guest user of the ticket
        // There can be many redeemRequests, we always show the last one of the collection.
        if ($mealTicket->getRedeemRequests()->count() > 0) {
            $redeemRequest = $mealTicket->getRedeemRequests()->last();
        } else {
            $redeemRequest = new RedeemRequest();
        }

        $redeemRequestForm = $this->createForm(
            'Mealmatch\CouponBundle\Form\RedeemRequestType',
            $redeemRequest,
            array(
                'action' => $this->generateUrl(
                    'api_mealticket_redeem_code',
                    array('mealTicket' => $mealTicket->getId())
                ),
                'method' => 'POST',
            )
        );

        $payinDataForm = $this->createForm(
            'Mealmatch\ApiBundle\Form\MealTicket\PayinRequiredDataType',
            array(
                'firstName' => $mealTicket->getGuest()->getProfile()->getFirstName(),
                'lastName' => $mealTicket->getGuest()->getProfile()->getLastName(),
                'address' => $mealTicket->getGuest()->getProfile()->getAddressLine1(),
                'postalCode' => $mealTicket->getGuest()->getProfile()->getAreaCode(),
                'city' => $mealTicket->getGuest()->getProfile()->getCity(),
                'region' => $mealTicket->getGuest()->getProfile()->getState(),
                'birthday' => $mealTicket->getGuest()->getProfile()->getBirthday(),
                'nationality' => $mealTicket->getGuest()->getProfile()->getNationality(),
                'countryOfResidence' => $mealTicket->getGuest()->getProfile()->getCountry(),
            ),
            array(
                'action' => $this->generateUrl(
                    'api_mealticket_prepare_payin',
                    array('mealTicket' => $mealTicket->getId())
                ),
                'method' => 'POST',
            )
        );

        return array(
            'ticket' => $mealTicket,
            'ticketForm' => $mealTicketForm->createView(),
            'redeemRequestForm' => $redeemRequestForm->createView(),
            'payinDataForm' => $payinDataForm->createView(),
            'viewData' => $viewData,
            'pTokens' => $mealTicket->getTransactions(),
            'print' => false,
        );
    }

    /**
     * Private helper to select the first mealOffer if it is NULL.
     *
     * @param ProMeal   $proMeal
     * @param MealOffer $mealOffer
     */
    private function selectFirstOfferIfNull(ProMeal $proMeal, MealOffer &$mealOffer): void
    {
        // If no offer ist choosen, the first mealOffer is selected.
        if (null === $mealOffer) {
            $this->logger->addWarning('No MealOffer selected!');
            /** @var MealOffer $mealOffer */
            $mealOffer = $proMeal->getMealOffers()->first();
            $this->logger->addWarning(
                sprintf('Auto-Selecting (%s) for ProMeal (%s)', $mealOffer->getId(), $proMeal->getId()
                )
            );
        }
    }
}
