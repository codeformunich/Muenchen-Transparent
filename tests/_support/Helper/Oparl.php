<?php
namespace Helper;

use Codeception\Lib\ModuleContainer;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Oparl extends \Codeception\Module
{
    // magic variable
    protected $requiredFields = ['updatejson'];

    // exposed through getters
    private $compressedResponse;
    private $prettyPrintedResponse;
    private $responseAsTree;

    // really private
    /** The path to the file with the expected response */
    private $url;
    /** Stores all URLs that have already gotten the basic checks to avoid redundant work */
    private $checked_urls = [];
    /** Stores the expected results in the format ["[url-affix]" => "expected json", ....] */
    private $expectedResults = [];

    /**
     * Returns the server response as pretty-printed json including decoded umlauts
     *
     * @return string
     */
    public function getPrettyPrintedResponse() {
        return $this->prettyPrintedResponse;
    }

    /**
     * Returns the server response as compressed json including decoded umlauts
     *
     * @return string
     */
    public function getCompressedResponse() {
        return $this->compressedResponse;
    }

    /**
     * Returns the server response as array-based tree
     *
     * @return array
     */
    public function getResponseAsTree() {
        return $this->responseAsTree;
    }

    /**
     * For debugging and developing it's often very usefull to have the
     * possibility to print something without having to use the debug flag
     */
    public function writeln($text = '') {
        $output = new \Codeception\Lib\Console\Output([]);
        $output->writeln($text);
    }

    public function _beforeSuite($settings = []) {
        $filename = codecept_data_dir() . "oparl_expected_results.txt";
        if (file_exists($filename)) {
            $content = file_get_contents($filename);
            preg_match_all('/^([^ ]+) (.+)$/m', $content, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $pretty_expected = stripslashes(json_encode(json_decode($match[2]), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $this->expectedResults[$match[1]] = $pretty_expected;
            }
        } else {
            if ($this->config['updatejson'] === true) {
                $this->writeln("\nCreating missing file with expected response ...");
                file_put_contents($filename, '\n');
            } else {
                $this->fail('The file with expected results is missing (' . $filename . ')');
            }
        }
    }

    public function _afterSuite() {
        $filename = codecept_data_dir() . "oparl_expected_results.txt";
        file_put_contents($filename, '');
        foreach ($this->expectedResults as $url => $pretty_expected) {
            $ugly_expected = stripslashes(json_encode(json_decode($pretty_expected), JSON_UNESCAPED_UNICODE));
            file_put_contents($filename, $url . ' ' . $ugly_expected . "\n", FILE_APPEND);
        }
    }

    /**
     * Sets filepath, uglyResponse, prettyResponse and tree
     */
    public function setVariables($url) {
        $this->checked_urls[] = $url;

        $this->url = $url;
        $this->compressedResponse = stripslashes(json_encode(json_decode($this->getModule('REST')->grabResponse()), JSON_UNESCAPED_UNICODE));
        $this->prettyPrintedResponse = stripslashes(json_encode(json_decode($this->getModule('REST')->grabResponse()), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->responseAsTree = json_decode($this->getCompressedResponse());
    }

    /**
     * Checks that response returned from the api matches the expected response stored in a file in the data directory
     *
     * When run with the `updatejson` environment all expected reponses that do not match the actual response overwritten
     */
    public function seeOParlFile() {
        if ($this->config['updatejson'] === true) {
            if (!array_key_exists($this->url, $this->expectedResults)) {
                $this->writeln("\nCreating expected response ...");
            } else if ($this->expectedResults[$this->url] != $this->getPrettyPrintedResponse()) {
                $this->writeln("\nUpdating expected response ...");
            } else {
                return;
            }

            $this->expectedResults[$this->url] = $this->getPrettyPrintedResponse();
        }

        if (!array_key_exists($this->url, $this->expectedResults)) {
            $this->fail('There\'s no expected response for this url: ' . $this->url);
        }

        $this->assertEquals($this->expectedResults[$this->url], $this->getPrettyPrintedResponse());
    }

    /**
     * @return bool
     */
    public function isURLKnown($url)
    {
        if (in_array($url, $this->checked_urls))
            return true;
        $this->checked_urls[] = $url;
        return false;
    }
}
