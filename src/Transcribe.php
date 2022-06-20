<?php

declare(strict_types=1);

namespace Maced0\TranscribeText;
class Transcribe
{
    private $config;
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function transcribe(string $text) {
        $rules = $this->config->getRules();

        foreach ($rules as $rule) {
            $result = $this->checkRule($rule, $text);

            if ($result['searchs'] == $result['matchs']) {
                return str_replace('\n', '
                ', $this->makeText($rule, $text));
            }
        }

        return '';
    }

    private function checkRule(array $rule, string $text) {
        $searchs = $rule['search'];

        $math = 0;

        foreach ($searchs as $search) {
            if (str_contains($text, $search)) { 
                $math++;
            }
        }

        return [
            'searchs' => count($searchs),
            'matchs' => $math
        ];
    }

    private function makeText(array $rule, string $text) {
        $variables = $this->getVariables($rule['variables'], $text);
        $outputText = $rule['outputText'];

        foreach ($variables as $name => $value) {
            $name = str_replace(' ', '', $name);
            $search = [
                '{{'.$name.'}}',
                '{{ '.$name.'}}',
                '{{ '.$name.' }}',
                '{{'.$name.' }}'
            ];
            $outputText = str_replace($search, $value, $outputText);
        }

        return $outputText;
    }

    private function getVariables(array $variables, $text) {
        $vars = [];
        $rows = [];
        preg_match_all('/.+/', $text, $rows);
        $rows = $rows[0];

        foreach ($rows as $key => $row) {
            foreach ($variables as $variable) {
                if (($pos = strpos($row, $variable['identifier'])) !== FALSE) { 
                    if (!isset($variable['where']) || $variable['where'] == "res_line")
                        $vars[$variable['name']] = substr($row, $pos+strlen($variable['identifier']));
                    if (isset($variable['where']) && $variable['where'] == "below_line") {
                        $count = 1;

                        if (isset($variable['count_lines'])) {
                            $count = $variable['count_lines'];
                        }

                        $variableText = '';

                        for ($i=1; $i <= $count; $i++) { 
                            $variableText .= trim($rows[$key+$i].'\n');
                        }

                        $vars[$variable['name']] = nl2br($variableText);
                    }
                }
            }
        }

        return $vars;
    }
}
