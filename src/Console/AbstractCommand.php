<?php

namespace Opendi\Solr\Client\Console;

use Opendi\Solr\Client\Client;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Event\HeadersEvent;
use GuzzleHttp\Exception\ParseException;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    private $client;

    protected function configure()
    {
        // Read defaults from env variables.
        // If they are not set, use some sensible defaults.
        $baseURL = getenv('OPENDI_SOLR_URL');
        if ($baseURL === false) {
            $baseURL = 'http://localhost:8983/solr/';
        }

        $username = getenv('OPENDI_SOLR_USER');
        if ($username === false) {
            $username = null;
        }

        $password = getenv('OPENDI_SOLR_PASS');
        if ($password === false) {
            $password = null;
        }

        $this
            ->addOption(
                'url',
                'u',
                InputOption::VALUE_REQUIRED,
                'Base solr URL',
                $baseURL
            )
            ->addOption(
                'username',
                null,
                InputOption::VALUE_REQUIRED,
                'Username for basic HTTP authentication',
                $username
            )
            ->addOption(
                'password',
                null,
                InputOption::VALUE_REQUIRED,
                'Password for basic HTTP authentication',
                $password
            );
    }

    protected function getClient(InputInterface $input, OutputInterface $output)
    {
        if (isset($this->client)) {
            return $this->client;
        }

        $baseURL = $input->getOption('url');
        $username = $input->getOption('username');
        $password = $input->getOption('password');

        // Add trailing slash if one doesn't exist
        if ($baseURL[strlen($baseURL) - 1] !== '/') {
            $baseURL .= '/';
        }

        $output->writeln("Solr URL: <info>$baseURL</info>");

        if (!empty($username)) {
            $output->writeln("Basic auth: <info>$username</info>");
        }

        $output->writeln("");

        // Guzzle options
        $options = ['base_url' => $baseURL];
        if (isset($username)) {
            $options['defaults']['auth'] = [$username, $password];
        }

        // Construct and return the client
        $guzzle = new GuzzleClient($options);

        $this->setupRequestLogging($guzzle, $output);

        return new Client($guzzle);
    }

    private function setupRequestLogging(GuzzleClient $guzzle, $output)
    {
        $emitter = $guzzle->getEmitter();

        //  Show the method and URL before each request
        $emitter->on('before', function (BeforeEvent $e) use ($output) {
            $url = $e->getRequest()->getUrl();
            $method = $e->getRequest()->getMethod();

            $output->write(sprintf("<info>%s</info> %s ", $method, $url));
        });

        // Show status code after the request
        $emitter->on('headers', function (HeadersEvent $e) use ($output) {
            $code = $e->getResponse()->getStatusCode();
            $reason = $e->getResponse()->getReasonPhrase();

            if ($code < 300) {
                $msg = "<info>$code $reason</info>";
            } else {
                $msg = "<error>$code $reason</error>";
            }

            $output->writeln($msg);
        });

        // On error, display the Solr error message from the response if
        // possible
        $emitter->on('error', function (ErrorEvent $e) use ($output) {
            $response = $e->getResponse();

            if ($response !== null) {
                // If there is a response, try to parse it to get the Solr error
                try {
                    $data = $response->json();

                    if (isset($data['error']['msg'])) {
                        $error = "Solr error: " . $data['error']['msg'];

                        $output-> writeln("");
                        $output-> writeln("<error>$error</error>");
                        return;
                    }
                } catch (ParseException $e) {
                    // Cannot parse :(
                }
            }
        });
    }
}
