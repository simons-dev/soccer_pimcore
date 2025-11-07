<?php

namespace App\Command;

use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pimcore\Model\DataObject\Folder;
use Pimcore\Model\DataObject\Team;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\DataObject\Data\GeoCoordinates;
use Carbon\Carbon;

#[AsCommand(
    name: 'import:team',
    description: 'Imports the football teams'
)]
class ImportTeamCommand extends AbstractCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $assetsFolder = $_SERVER['DOCUMENT_ROOT'] . "public/var/assets/";
        $filename = "teams.csv";
        $fileLocation = $assetsFolder . $filename;
        
        try{
            $row = 1;
            if (($handle = fopen($fileLocation, "r")) !== FALSE){
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if($row > 1){
                        $team = new Team();
                        $team->setKey(\Pimcore\Model\Element\Service::getValidKey($data[0], 'object'));
                        $teamsFolder = Folder::getByPath('/Teams');
                        $team->setParentId($teamsFolder->getId());

                        $team->setName($data[0]);
                        $team->setLogo(Image::getByPath($data[1]));
                        $team->setTrainer($data[2]);
                        $team->setCity($data[3]);
                        $team->setLocation(new GeoCoordinates($data[4], $data[5]));
                        $team->setEstablished(Carbon::createFromDate($data[6], $data[7], $data[8]));
                        $team->setPublished(true);
                        $team->save();
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