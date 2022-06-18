<?php

declare(strict_types=1);

namespace Maced0\TranscribeText;

class Config
{
    private $data = [];

    public function addRule(array $search, array $variables, string $outputText) {
        $error = '';
        
        foreach ($search as $value) {
            if (!is_string($value)) {
                $error = 'badly formatted search column';
                break;
            }
        }

        foreach ($variables as $value) {
            if (
                !isset($value['identifier'])
                || !is_string($value['identifier'])
                || !isset($value['name'])
                || !is_string($value['name'])
            
            ) {
                $error = 'badly formatted variables column';
                break;
            }
        }

        if (!$outputText) {
            $error = 'outputText is required';
        }

        if (!count($search)) {
            $error = 'search column is required';
        }

        if (!count($variables)) {
            $error = 'search column is required';
        }


        if ($error) {
            return [
                'status' => 0,
                'message' => $error
            ];
        }


        $this->data[] = [
            'search'     => $search,
            'variables'  => $variables,
            'outputText' => $outputText
        ];

        return ['status' => 1];
    }

    public function getRules() {
        return $this->data;
    }
}