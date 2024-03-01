<?php

namespace App\Controller;

use App\Entity\Game;
use App\Form\GameType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class GameController extends AbstractController
{
    #[Route('/game', name: 'app_game')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $game = $entityManager->getRepository(Game::class)->findAll();
        return $this->render('game/index.html.twig', [
            'games' => $game,
        ]);
    }

    #[Route('/game/show/{slug}', name: 'app_game_show')]
    public function show(EntityManagerInterface $entityManager, Game $game): Response
    {
        $games = $entityManager->getRepository(Game::class)->find($game);
        return $this->render('game/show.html.twig', [
            'game' => $games,
        ]);
    }

    #[Route('/game/delete/{slug}', name: 'app_game_delete')]
    public function delete(EntityManagerInterface $entityManager, string $slug, Request $r): Response
    {
        $user = $this->getUser();
        if ($user === null) {
            return $this->redirectToRoute('app_login');
        }
        elseif ($user->getRoles()[0] !== 'ROLE_ADMIN') {
            return $this->redirectToRoute('app_game');
        }
        $game = $entityManager->getRepository(Game::class)->findOneBy(['slug' => $slug]);
        if ($this->isCsrfTokenValid('delete'.$game->getId(), $r->request->get('csrf'))) {
            $entityManager->remove($game);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_game');
    }
    #[Route('/game/edit/{slug}', name: 'app_game_edit')]
    public function edit(EntityManagerInterface $entityManager, string $slug, Request $request, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        if ($user === null) {
            return $this->redirectToRoute('app_login');
        }
        elseif ($user->getRoles()[0] !== 'ROLE_ADMIN') {
            return $this->redirectToRoute('app_game');
        }
        $game = $entityManager->getRepository(Game::class)->findOneBy(['slug' => $slug]);
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slugy = $slugger->slug($game->getTitle());
            $game->setSlug($slugy);
            $entityManager->persist($game);
            $entityManager->flush();
            return $this->redirectToRoute('app_game_dashboard');
        }
        return $this->render('game/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/game/new', name: 'app_game_new')]
    public function new(EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        if ($user === null) {
            return $this->redirectToRoute('app_login');
        }
        elseif ($user->getRoles()[0] !== 'ROLE_ADMIN') {
            return $this->redirectToRoute('app_game');
        }
        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugger->slug($game->getTitle());
            $game->setSlug($slug);
            $entityManager->persist($game);
            $entityManager->flush();
            return $this->redirectToRoute('app_game');
        }
        return $this->render('game/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/game/dashboard', name: 'app_game_dashboard')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ($user === null) {
            return $this->redirectToRoute('app_login');
        }
        elseif ($user->getRoles()[0] !== 'ROLE_ADMIN') {
            return $this->redirectToRoute('app_game');
        }
        $game = $entityManager->getRepository(Game::class)->findAll();
        return $this->render('game/dashboard.html.twig', [
            'games' => $game,
        ]);
    }
}
