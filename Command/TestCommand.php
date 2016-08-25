<?php

namespace Haswalt\QueueBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class TestCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('queue:test');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $channels = $this->getContainer()->getParameter('haswalt_queue.channels');
        $exchanges = $this->getContainer()->getParameter('haswalt_queue.exchanges');

        foreach ($channels as $id) {
            $output->writeln(sprintf("Testing %s:", $id));
            $channel = $this->getContainer()->get(sprintf("haswalt_queue.channel.%s", $id));

            if (!isset($exchanges[$id])) {
                $output->writeln("\tSKIPPING");
                continue;
            }

            foreach ($exchanges[$id] as $exchange) {
                $output->writeln(sprintf("\tSending message to %s", $exchange));
                $msg = new AMQPMessage('test', [
                    'content_type' => 'text/plain',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                ]);

                $channel->basic_publish($msg, $exchange, 'testing');
            }
        }
    }
}
