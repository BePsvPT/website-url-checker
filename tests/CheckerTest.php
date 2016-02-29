<?php

use Bepsvpt\WebsiteUrlChecker\Checker;

class CheckerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_render_correct_configs()
    {
        $this->assertEquals([
            'deep' => 3,
            'timeout' => 10.0,
        ], (new Checker())->getConfig());

        $this->assertEquals([
            'deep' => 5,
            'timeout' => 10.0,
        ], (new Checker(['deep' => 5]))->getConfig());

        $this->assertEquals([
            'deep' => 3,
            'timeout' => 7.5,
        ], (new Checker(['timeout' => 7.5]))->getConfig());

        $this->assertEquals([
            'deep' => 4,
            'timeout' => 3.0,
        ], (new Checker(['deep' => 4, 'timeout' => 3.0]))->getConfig());
    }

    /**
     * @test
     */
    public function getDomUrls_should_return_all_links_from_html_dom()
    {
        $dom = <<<EOF
<!doctype html>
<html>
    <body>
        <a href="https://www.google.com">google</a>
        <a href="/link1.php">link1</a>
        <a href="link2.php">link2</a>
        <a href="./link3.php">link3</a>
        <a href="../link4.php">link4</a>
        <!--
            <img src="/logo.png">
            <a href="./link5.php">link5</a>
        -->
    </body>
</html>
EOF;

        $checker = (new Checker)->setUrl('http://localhost/');

        $this->assertEquals([
            ['url' => 'https://www.google.com', 'external' => false],
            ['url' => 'http://localhost/link1.php', 'external' => false],
            ['url' => 'http://localhost/hello/world/link2.php', 'external' => false],
            ['url' => 'http://localhost/hello/world/link3.php', 'external' => false],
            ['url' => 'http://localhost/hello/link4.php', 'external' => false],
        ], $checker->getDomUrls('http://localhost/hello/world/', $dom));

        $this->assertEquals([
            ['url' => 'https://www.google.com', 'external' => true],
            ['url' => 'https://www.google.com/link1.php', 'external' => true],
            ['url' => 'https://www.google.com/hello/world/link2.php', 'external' => true],
            ['url' => 'https://www.google.com/hello/world/link3.php', 'external' => true],
            ['url' => 'https://www.google.com/hello/link4.php', 'external' => true],
        ], $checker->getDomUrls('https://www.google.com/hello/world/', $dom));
    }
}