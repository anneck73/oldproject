<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\PayPalBundle\Controller;

use Doctrine\ORM\EntityManager;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealTicket;
use Mealmatch\PayPalBundle\PayPalConstants;
use MMApiBundle\Entity\MealTicket;
use MMUserBundle\Entity\MMUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * This is the one PayPalController to a) redirect and b) receive paypal notification signals.
 *
 * @Route("/payment")
 */
class PayPalController extends Controller
{
    /**
     * @Route("/paypal/notify/{hash}", name="paypal_notify")
     * @Method(methods={"POST", "GET", "HEAD"})
     *
     * @param Request $request
     * @param string  $hash
     *
     * @return Response|ResourceNotFoundException
     */
    public function onNotifyAction(Request $request, string $hash)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->get('doctrine.orm.default_entity_manager');
        /** @var MMUser $systemUser */
        $systemUser = $entityManager->getRepository('MMUserBundle:MMUser')->findOneByUsername('SYSTEM');

        // to enable Workflow transitions we need a user to execute them, we have a special SYSTEM user for that.
        $this->systemUserLogin($systemUser);

        // Get The MealTicket associated with the hash from return-url/success
        /** @var BaseMealTicket $mealTicket */
        $mealTicket = $entityManager->getRepository('ApiBundle:Meal\BaseMealTicket')->findOneBy(
            array('hash' => $hash)
        )
        ;

        if (null === $mealTicket) {
            // Don't process, just Return AND write a log ...
            $this->get('logger')->addWarning('Mealticket not found! #'.$mealTicket->getNumber());

            return new ResourceNotFoundException('Nothing there!');
        }

        // We found a Mealticket ... process it ...
        $this->get('logger')->addInfo('Mealticket processing #'.$mealTicket->getNumber());

        // Process all incoming request variables ...
        $postVarJson = json_encode($request->request->all());
        $postVarArr = json_decode($postVarJson, true);

        // Create a paymentToken from the PayPal notification ...
        $paymentNotifyToken = $this->get('mealmatch_paypal.payment_token')->createOnNotify(
            $mealTicket,
            $postVarArr
        )
        ;

        // Update the MealTicket using the paypal payment token
        $this->get('mealmatch_paypal.manager')->updateMealTicketOnNotify(
            $mealTicket,
            $paymentNotifyToken
        );

        // And persist everything ...
        $entityManager->flush();

        $this->get('logger')->addInfo('Mealticket onNotify #'.$mealTicket->getNumber().' '.$paymentNotifyToken->getTokenStatus());

        // @TODO: Is this a correct repsonse to the PayPal call?
        return new Response(
            'OK'
        );
    }

    /**
     * @Route("/paypal/cancel/{hash}", name="paypal_cancel")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function onCancelAction(Request $request, string $hash)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');
        /**
         * Get The MealTicket associated with the hash from return-url/success.
         */

        /** @var MealTicket $mealTicket */
        $mealTicket = $em->getRepository('ApiBundle:Meal\BaseMealTicket')->findOneBy(
            array('hash' => $hash)
        );

        // Fail fast if ticket is not found by hash ...
        if (null === $mealTicket) {
            throw new ResourceNotFoundException('What?');
        }

        $this->get('logger')->addInfo(PayPalConstants::logPrefix($mealTicket->getNumber()).'MealTicketController->onCancel: '.$mealTicket);
        // redirect him to that meal ticket after the user hit "cancle" ...
        return $this->redirectToRoute('api_mealticket_show', array('id' => $mealTicket->getId()));
    }

    /**
     * The success RETURN URL for paypal.
     *
     * @Route("/paypal/success/{hash}", name="paypal_success")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function onSuccessAction(Request $request, string $hash)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');

        /**
         * Get The MealTicket associated with the hash from return-url/success.
         */
        /** @var MealTicket $mealTicket */
        $mealTicket = $em->getRepository('ApiBundle:Meal\BaseMealTicket')->findOneBy(
            array('hash' => $hash)
        )
        ;

        // (!) This action may happen after or before onNotify is called (!)
        $this->get('logger')->addInfo(PayPalConstants::logPrefix($mealTicket->getNumber()).'onSuccessAction on MealTicketStatus: '.$mealTicket->getStatus());
        // redirect to MealTicket associated to this hash ...
        return $this->redirectToRoute(
            'api_mealticket_show',
            array('id' => $mealTicket->getId())
        );
    }

    /**
     * Forces a named user login.
     *
     * @param string $systemUser
     */
    private function systemUserLogin($systemUser): void
    {
        // There is no logged in user, and this is a system call
        // the firewall context (defaults to the firewall name)
        $firewall = 'main';
        $token = new UsernamePasswordToken($systemUser, $systemUser->getPassword(), $firewall, $systemUser->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $this->get('fos_user.security.login_manager')->logInUser($firewall, $systemUser);
    }
}
