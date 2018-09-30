<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Place;
use AppBundle\Form\PartnerType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Entity\Partner;


/**
 * Class PartnerController
 * @package AppBundle\Controller
 */
class PartnerController extends Controller
{
    /**
     * @Rest\View(serializerGroups={"place"})
     * @Rest\Get("/users/{id}/suggestions")
     */
    public function getUserSuggestionsAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Partner')
            ->find($request->get('id'));
        /* @var $user Partner */

        if (empty($user)) {
            return $this->userNotFound();
        }

        $suggestions = [];

        $places = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Place')
            ->findAll();
        /* @var $place Place */

        foreach ($places as $place) {

            $user->preferencesMatch($place->getThemes());
                $suggestions[] = $place;

        }

        return $suggestions;
    }


    private function userNotFound()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/users")
     */
    public function postUsersAction(Request $request)
    {
        $user = new Partner();
        $form = $this->createForm(PartnerType::class, $user);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
            return $user;
        } else {
            return $form;
        }
    }

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Get("/users")
     */
    public function getListUsersAction()
    {
        $users = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:Partner')
                ->findAll();
        /* @var $users Partner[] */

        return $users;
    }

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Get("/users/{user_id}")
     */
    public function getUserAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:Partner')
                ->find($request->get('user_id'));
        /* @var $user Partner */

        if (empty($user)) {
            return \FOS\RestBundle\View\View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return $user;
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/users/{id}")
     */
    public function removeUserAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $place = $em->getRepository('AppBundle:Partner')
            ->find($request->get('id'));
        /* @var $place Partner */

        $em->remove($place);
        $em->flush();
    }

}
