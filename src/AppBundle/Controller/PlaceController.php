<?php

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use AppBundle\Entity\Place;



/**
 * Class PlaceController
 * @package AppBundle\Controller
 */
class PlaceController extends Controller
{

    /**
     * @Get("/import/places")
     */
    public function importPlacesFromJson()
    {
        $str = file_get_contents('https://www.data.gouv.fr/fr/datasets/r/5927eba6-6375-4d26-87a4-6ddf7fb493a9');
        $json = json_decode($str , true);

        $entityManager = $this->getDoctrine()->getManager();

        for ($i = 0; $i < count($json); $i++) {
            foreach ($json as $row) {

                $place = new Place;

                $place->setAddress($row['fields']['ville']);
                $place->setName($row['fields']['etablissement']);
                $place->setHandicapMoteur($row['fields']['handicap_moteur']);
                $place->setCreatedAt(new \DateTime('now'));

                $entityManager->persist($place);
                $entityManager->flush();
            }
        }

        return new JsonResponse('ok');
    }

    /**
     * @Rest\View(serializerGroups={"place"})
     * @Rest\Get("/places")
     */
    public function getlistPlaces()
    {
        $places = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Place')
            ->findAll();
        /* @var $places Place[] */

        return $places;

    }

    /**
     * @Rest\View(serializerGroups={"place"})
     * @Rest\Get("/places/{id}")
     */
    public function getPlaceAction(Request $request)
    {
        $place = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Place')
            ->find($request->get('id'));

        if (empty($place)) {
            return \FOS\RestBundle\View\View::create(['message' => 'Place not found'], Response::HTTP_NOT_FOUND);
        }
        /* @var $formatted Place[] */
        return $place;

    }

    /**
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT, serializerGroups={"place"})
     * @Rest\Delete("/places/{id}")
     */
    public function removePlaceAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $place = $em->getRepository('AppBundle:Place')
            ->find($request->get('id'));
        /* @var $place Place */

        $em->remove($place);
        $em->flush();
    }

}