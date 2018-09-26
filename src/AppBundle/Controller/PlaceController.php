<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Place;
use Symfony\Component\HttpFoundation\Response;

class PlaceController extends Controller
{
  /**
     * @Route("/places", name="places_list")
     * @Method({"GET"})
     */
    public function getPlacesAction(Request $request)
    {
        $str = file_get_contents('https://www.data.gouv.fr/fr/datasets/r/5927eba6-6375-4d26-87a4-6ddf7fb493a9');
        $json = json_decode($str, true);

        $places = [];

        foreach ($json as $key => $value) {
            $places['handicap_moteur'] = $value['fields']['handicap_moteur'];
            $places['lieu'] = $value['fields']['etablissement'];
            $places['ville'] = $value['fields']['ville'];

        }
        var_dump(count($places));
        $place = new Place;

        $place->setAddress($places["ville"]);
        $place->setName($places["lieu"]);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($place);
        $entityManager->flush();


        $places = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Place')
            ->findAll();
        /* @var $places Place[] */

        $formatted = [];
        foreach ($places as $place) {
            $formatted[] = [
                'id' => $place->getId(),
                'name' => $place->getName(),
                'address' => $place->getAddress(),
            ];
        }


        return new JsonResponse($formatted);
    }


    public static function extract()
    {
        $str = file_get_contents('https://www.data.gouv.fr/fr/datasets/r/5927eba6-6375-4d26-87a4-6ddf7fb493a9');
        $json = json_decode($str, true);

        $places = [];

        foreach ($json as $key => $value) {
            $places['handicap_moteur'] = $value['fields']['handicap_moteur'];
            $places['lieu'] = $value['fields']['etablissement'];
            $places['ville'] = $value['fields']['ville'];

        }

        return $places;
    }

    public function persist($places)
    {
        $place = new Place;

        $place->setAddress($places["ville"]);
        $place->setName($places["lieu"]);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($place);
        $entityManager->flush();

        var_dump(new Response('Saved new product with id '.$place->getId()));

    }

}