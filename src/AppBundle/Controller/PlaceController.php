<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Place;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PlaceController
 * @package AppBundle\Controller
 */
class PlaceController extends Controller
{


    /**
     * @Route("/import/places", name="places_extract")
     * @Method({"GET"})
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
     * @Route("/list", name="places_list")
     * @Method({"GET"})
     */
    public function getlistPlaces()
    {
        $places = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Place')
            ->findAll();
        /* @var $places Place[] */

        $formatted = [];
        foreach ($places as $place) {
            $formatted[] = [
                'id' => $place->getId() ,
                'name' => $place->getName() ,
                'address' => $place->getAddress() ,
                'handicap_moteur' => $place->getHandicapMoteur() ,
                'created_at' => $place->getHandicapMoteur() ,
            ];
        }


        return new JsonResponse($formatted);

    }


    /**
     * @Route("/places/{place_id}", name="places_one")
     * @Method({"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getPlaceAction(Request $request)
    {
        $place = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Place')
            ->find($request->get('place_id'));

        if (empty($place)) {
            return new JsonResponse(['message' => 'Place not found'] , Response::HTTP_NOT_FOUND);
        }
        /* @var $formatted Place[] */

        $formatted = [
            'id' => $place->getId() ,
            'name' => $place->getName() ,
            'address' => $place->getAddress() ,
        ];

        return new JsonResponse($formatted);
    }

}