<?php	

namespace LaPoiz\WindBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use LaPoiz\WindBundle\core\maree\MareeGetData;


class DeleteMareeCommand extends ContainerAwareCommand  {

 	protected function configure()
    {
        $this
            ->setName('lapoiz:deleteMaree')
            ->setDescription('Delete marre from Data Base')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
		// get list of URL 
    	$em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // Get Marée
        $spotList = $em->getRepository('LaPoizWindBundle:Spot')->findAll();

        $output->writeln('<info>************** DELETE MAREE ****************</info>');
        foreach ($spotList as $spot) {
            if ($spot->getMareeURL()!=null) {
                MareeGetData::deleteMaree($spot,$em, $output);
            }
        }
    }
}