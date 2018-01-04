<?php
/**
 * Created by mr.Umnik.
 */

namespace MoneyStat\Controller;


use MoneyStat\Parser\Sberbank;
use Psr\Container\ContainerInterface;

class CheckEmail
{
	protected $container;

	public function __construct(ContainerInterface $c)
	{
		$this->container = $c;
	}

	public function __invoke($request, $response, $args)
	{
		$settings = $this->container['settings']['email'];
		$mailbox = new \PhpImap\Mailbox(
			$settings['host'],
			$settings['user'],
			$settings['password'],
			$settings['attachments_dir']
		);
		$mailsIds = $mailbox->searchMailbox('UNSEEN');
		if (!$mailsIds) {
			$this->container->logger->debug('Unread messages not found in mailbox');
		} else {
			foreach ($mailsIds as $mailId) {
				$mail = $mailbox->getMail($mailId);
				$mailbox->markMailAsRead($mailId);
				if (strpos($mail->subject, 'Statement report') !== false) {
					$attachments = $mail->getAttachments();
					if (!empty($attachments)) {
						foreach ($attachments as $attachment) {
							if (substr($attachment->name, -4) == '.txt') {
								$this->container->logger->info('Found new attachment for parse: ' . $attachment->filePath);
								$parser = new Sberbank();
								$parser->parse($attachment->filePath);
							}
						}
					}
				}
			}
		}


		return $response;
	}
}