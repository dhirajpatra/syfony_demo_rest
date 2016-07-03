<?php

namespace Acme\TestBundle\Tests\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/acme/test/default/index');

        $this->assertContains('Hello World', $client->getResponse()->getContent());
    }
}
