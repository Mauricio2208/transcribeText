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

    public function transcribe(string $text, string $outputText) {
        $rules = $this->config->getRules();

        foreach ($rules as $rule) {
            $result = $this->checkRule($rule, $text);

            if ($result['searchs'] == $result['matchs']) {
                return $this->makeText($rule, $text, $outputText);
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

    private function makeText(array $rule, string $text, string $outputText) {
        $variables = $this->getVariables($rule['variables'], $text);
        
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

        foreach ($rows as $row) {
            foreach ($variables as $variable) {
                if (($pos = strpos($row, $variable['identifier'])) !== FALSE) { 
                    $vars[$variable['name']] = substr($row, $pos+strlen($variable['identifier']));
                }
            }
        }

        return $vars;
    }
}
