<?php

namespace App\Controller;

use App\Form\TrackType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/include')]
class IncludeController extends AbstractController
{
    #[Route('/search-form', name: 'app_include_search_form', methods: ['GET', 'POST'])]
    public function searchForm(Request $request): Response
    {
        $form = $this->createForm(TrackType::class, null, [
            'action' => $this->generateUrl('app_include_search_form'),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            return $this->redirectToRoute('app_track_index', ['search' => $data['recherche']]);
        }

        return $this->render('include/search.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}