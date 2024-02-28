<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMWebFrontBundle\Controller;

use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\Services\MealService;
use Mealmatch\GameLogicBundle\Core\Score;
use MMUserBundle\Entity\MMUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicUserController extends ApiController
{
    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Request $pRequest
     * @param string  $pHash
     * @param string  $myArgument with a *description* of this argument, these may also
     *                            span multiple lines
     *
     * @Route("/p/{pHash}/hash", name="public_user_by_hash")
     */
    public function showUserByHash(Request $request, string $pHash)
    {
        $this->init();

        $userAccount = $this->getUserAccountByHash($pHash);

        return $this->renderPublicShow($request, $userAccount);
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param Request $pRequest
     * @param string  $pName
     * @param string  $myArgument with a *description* of this argument, these may also
     *                            span multiple lines
     *
     * @Route("/p/{pName}/name", name="public_user_by_username")
     *
     * @return Response
     */
    public function showUserByUsername(Request $request, string $pName)
    {
        $this->init();

        $userAccount = $this->getUserAccountByName($pName);

        return $this->renderPublicShow($request, $userAccount);
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $pHash
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return MMUser
     */
    private function getUserAccountByHash(string $pHash): MMUser
    {
        $userAccount = $this->em->getRepository('MMUserBundle:MMUser')->findOneByHash(
            array(
                'username' => $pHash,
            )
        );

        return $userAccount;
    }

    /**
     * Public display of the specified user account PROFILE page.
     *
     * The method uses the request to aquire the USER_ROLE of the $userAccount. The user's role determines which TWIG
     * template is used. Each USER_ROLE has its own template!
     *
     * @param Request $request     the current request
     * @param MMUser  $userAccount the owner of the profile
     *
     * @return Response the http response for the client
     */
    private function renderPublicShow(Request $request, MMUser $userAccount): Response
    {
        // the userAccount has scores ...
        $userScores = $this->getUserScores($userAccount);
        // the userAccount has a profile ...
        $userProfile = $userAccount->getProfile();
        // the restaurant user also has a restaurant profile...
        $restaurantProfile = $userAccount->getRestaurantProfile();
        // and settings
        $userSettings = $userAccount->getSettings();

        // Get the running meals (if any) hosted by the userAccount
        // Make use of a service to obtain all data we need inside the view.
        /** @var MealService $mealmatchMealservice */
        $mealmatchMealservice = $this->get('api.meal.service');

        // get all meals, running and hosted by the userAccount, to show which meals the userAccount offers.
        // e.g. the user is the HOST
        $hostedMeals = $mealmatchMealservice->getRunningByUser($userAccount);

        // ViewData for a standard user profile
        $homeUserAccountViewData = array(
            'uProfile' => $userProfile,
            'mHosted' => $hostedMeals,
            'currentUser' => $this->getUser(),
            'uSettings' => $userSettings,
            'uAccount' => $userAccount,
            'uScores' => $userScores,
            'selectedTab' => $this->getSelectedTab($request),
        );

        // ViewData for a restaurant profile
        $restaurantUserAccountViewData = array(
            'uProfile' => $userProfile,
            'rProfile' => $restaurantProfile,
            'mHosted' => $hostedMeals,
            'uSettings' => $userSettings,
            'uAccount' => $userAccount,
            'uScores' => $userScores,
            'selectedTab' => $this->getSelectedTab($request),
        );
        // the templates to choose from
        $userProfileView = '@WEBUI/UserProfile/public.html.twig';
        $restaurantProfileView = '@WEBUI/RestaurantProfile/public.html.twig';

        // Default template is for the userAccount
        $viewTemplate = $userProfileView;
        // The default ViewData:
        $viewData = $homeUserAccountViewData;

        // If the userAccount is a Restaurant ...
        if ($userAccount->hasRole('ROLE_RESTAURANT_USER')) {
            // ... change the template
            $viewTemplate = $restaurantProfileView;
            // ... change view data
            $viewData = $restaurantUserAccountViewData;
        }

        $viewData['viewData'] = array(
           'title' => 'Default',
        );

        // Render the combination and return!
        return $this->render($viewTemplate, $viewData);
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $pName
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return array
     */
    private function getUserScores(MMUser $pUserAccount): array
    {
        $userScoresRaw = $this->em->getRepository('MMGameLogicBundle:Score')->findScoresForUser($pUserAccount);

        $userScores = array(
            'logins' => 0,
            'joined' => 0,
            'hosted' => 0,
            'created' => 0,
        );
        /** @var Score $score */
        foreach ($userScoresRaw as $score) {
            if ('login' === $score->getName()) {
                $userScores['logins'] = $score->getValue();
            }
            if ('MealCreated' === $score->getName()) {
                $userScores['created'] = $score->getValue();
            }
            if ('MealJoined' === $score->getName()) {
                $userScores['joined'] = $score->getValue();
            }
            if ('MealHosted' === $score->getName()) {
                $userScores['hosted'] = $score->getValue();
            }
        }

        return $userScores;
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $pName
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return MMUser
     */
    private function getUserAccountByName(string $pName): MMUser
    {
        $userAccount = $this->em->getRepository('MMUserBundle:MMUser')->findOneBy(
            array(
                'username' => $pName,
            )
        );

        return $userAccount;
    }
}
