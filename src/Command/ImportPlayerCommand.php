<?php

namespace App\Command;

use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pimcore\Model\DataObject\Folder;
use Pimcore\Model\DataObject\Team;
use Pimcore\Model\DataObject\Player;

#[AsCommand(
    name: 'import:player',
    description: 'Imports the players'
)]
class ImportPlayerCommand extends AbstractCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $assetsFolder = $_SERVER['DOCUMENT_ROOT'] . "public/var/assets/";
        $filename = "players.csv";
        $fileLocation = $assetsFolder . $filename;
        
        try{
            $row = 1;
            if (($handle = fopen($fileLocation, "r")) !== FALSE){
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if($row > 1){
                        $player = new Player();
                        $player->setKey(\Pimcore\Model\Element\Service::getValidKey($data[0], 'object'));
                        $playersFolder = Folder::getByPath('/Players');
                        $player->setParentId($playersFolder->getId());

                        $player->setPlayerId($data[0]);
                        $player->setFirstname($data[1]);
                        $player->setLastname($data[2]);
                        $player->setNumber($data[3]);
                        $player->setAge($data[4]);
                        $player->setPosition($data[5]);
                        $player->setTeam(Team::getByName($data[6], 1));

                        $player->setPublished(true);
                        $player->save();
                    }
                    $row++;
                }
                fclose($handle);
            }
            $this->writeInfo("successfull imported");
            return AbstractCommand::SUCCESS;
        }
        catch(Exception $e){
            $this->writeError("failed with:" . $e->getMessage());
            return AbstractCommand::FAILURE;
        }

    }
}