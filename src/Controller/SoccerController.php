<?php

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pimcore\Model\DataObject\Player;
use Pimcore\Model\DataObject\Team;

class SoccerController extends FrontendController
{
    public function defaultAction(Request $request): Response
    {
        $teams = new Team\Listing();
        //create an associative array where key is teamName and value is number of Players
        $numberOfPlayers = array();
        foreach($teams as $team){
            $playersOfTeam = new Player\Listing();
            $playersOfTeam->setCondition("team__id = ?" , $team->getId() );

            $numberOfPlayers[$team->getName()] = count($playersOfTeam);
        }

        return $this->render('soccer/overview.html.twig', [
            "teams" => $teams,
            "players" => $numberOfPlayers
        ]);
    }

    public function detailAction(Request $request, string $teamPath) : Response
    {
        $team = Team::getByPath("/Teams/" . $teamPath);
        if($team == null){
            return $this->render('soccer/invalid.html.twig');
        }
        $players = new Player\Listing();
        //get all players of that team
        $players->setCondition("team__id = ?" , $team->getId() );

        return $this->render('soccer/detail.html.twig', [
            'team' => $team,
            'players' => $players
        ]);
    }
}