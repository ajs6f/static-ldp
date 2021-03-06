<?php
/**
 * Created by PhpStorm.
 * User: whikloj
 * Date: 2017-04-06
 * Time: 9:26 PM
 */

namespace Trellis\StaticLdp\Model;

use Trellis\StaticLdp\StaticLdpTestBase;

/**
 * @coversDefaultClass \Trellis\StaticLdp\Model\BasicContainer
 * @group unittest
 */
class BasicContainerTest extends StaticLdpTestBase
{

    /**
     * @var \Symfony\Component\BrowserKit\Client
     */
    protected $client;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->client = $this->createClient();
    }

    /**
     * Test Get of RDF directory listing/ BasicContainer
     * @covers ::get
     * @covers ::__construct
     * @covers ::getEtag
     */
    public function testGetTurtle()
    {
        $expected_links = [
            "<" . Resource::LDP_NS . "Resource>; rel=\"type\"",
            "<" . Resource::LDP_NS . "BasicContainer>; rel=\"type\""
        ];
        $expected_vary = "Accept";
        $request_mime = "text/turtle";

        $this->client->request('GET', "/", [], [], ['HTTP_ACCEPT' => $request_mime]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "GET should be allowed.");
        $response = $this->client->getResponse();
        $charset = $response->getCharset();
        $expected_mime = "{$request_mime}; charset={$charset}";

        $this->assertTrue($response->headers->has('Link'), "Missing Link header");
        $this->assertEquals($expected_links, $response->headers->get("Link", null, false), "Link headers incorrect.");

        $this->assertTrue($response->headers->has('Vary'), "Missing Vary header");
        $this->assertEquals($expected_vary, $response->headers->get('Vary'), "Vary headers incorrect.");

        $this->assertTrue($response->headers->has("Content-Type"), "Missing Content-Type header");
        $this->assertEquals($expected_mime, $response->headers->get('Content-Type'), "Content-Type header incorrect");

        $this->assertTrue($response->headers->has("etag"), "Missing Etag header.");
        $etag = $response->headers->get('etag');

        $this->client->request('GET', "/", [], [], ['HTTP_ACCEPT' => $expected_mime]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "GET should be allowed a second time.");
        $this->assertTrue($this->client->getResponse()->headers->has("etag"), "Missing Etag header.");
        $this->assertEquals($etag, $this->client->getResponse()->headers->get('etag'), "Etags don't match.");

        $subject = "http://localhost/";
        $contains = "http://www.w3.org/ns/ldp#contains";
        $graph = new \EasyRdf_Graph();
        $graph->parse($response->getContent(), "turtle", $subject);
        $this->assertEquals(5, $graph->countTriples());
        $this->assertTrue($graph->hasProperty($subject, "http://purl.org/dc/terms/modified"));
        $this->assertTrue($graph->isA($subject, "http://www.w3.org/ns/ldp#BasicContainer"));
        $this->assertTrue($graph->isA($subject, "http://www.w3.org/ns/ldp#Resource"));
        $this->assertTrue($graph->hasProperty($subject, $contains, $graph->resource("http://localhost/nobel_914.ttl")));
        $this->assertTrue($graph->hasProperty($subject, $contains, $graph->resource("http://localhost/riel.jpg")));
    }

    /**
     * Test Get of RDF directory listing/ BasicContainer
     * @covers ::get
     * @covers ::__construct
     * @covers ::getEtag
     */
    public function testGetJsonLD()
    {
        $expected_links = [
            "<" . Resource::LDP_NS . "Resource>; rel=\"type\"",
            "<" . Resource::LDP_NS . "BasicContainer>; rel=\"type\""
        ];
        $expected_vary = "Accept";
        $expected_mime = "application/ld+json";

        $this->client->request('GET', "/", [], [], ['HTTP_ACCEPT' => $expected_mime]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "GET should be allowed.");
        $response = $this->client->getResponse();

        $this->assertTrue($response->headers->has('Link'), "Missing Link header");
        $this->assertEquals($expected_links, $response->headers->get("Link", null, false), "Link headers incorrect.");

        $this->assertTrue($response->headers->has('Vary'), "Missing Vary header");
        $this->assertEquals($expected_vary, $response->headers->get('Vary'), "Vary headers incorrect.");

        $this->assertTrue($response->headers->has("Content-Type"), "Missing Content-Type header");
        $this->assertEquals($expected_mime, $response->headers->get('Content-Type'), "Content-Type header incorrect");

        $this->assertTrue($response->headers->has("etag"), "Missing Etag header.");
        $etag = $response->headers->get('etag');

        $this->client->request('GET', "/", [], [], ['HTTP_ACCEPT' => $expected_mime]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "GET should be allowed a second time.");
        $this->assertTrue($this->client->getResponse()->headers->has("etag"), "Missing Etag header.");
        $this->assertEquals($etag, $this->client->getResponse()->headers->get('etag'), "Etags don't match.");

        $this->assertJson($this->client->getResponse()->getContent(), "Is not valid JSON");
        $json = json_decode($this->client->getResponse()->getContent());

        $subject = "http://localhost/";
        $contains = "http://www.w3.org/ns/ldp#contains";
        $graph = new \EasyRdf_Graph();
        $graph->parse($json, "jsonld", $subject);
        $this->assertEquals(5, $graph->countTriples());
        $this->assertTrue($graph->hasProperty($subject, "http://purl.org/dc/terms/modified"));
        $this->assertTrue($graph->isA($subject, "http://www.w3.org/ns/ldp#BasicContainer"));
        $this->assertTrue($graph->isA($subject, "http://www.w3.org/ns/ldp#Resource"));
        $this->assertTrue($graph->hasProperty($subject, $contains, $graph->resource("http://localhost/nobel_914.ttl")));
        $this->assertTrue($graph->hasProperty($subject, $contains, $graph->resource("http://localhost/riel.jpg")));
    }
}
