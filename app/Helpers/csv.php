<?php

namespace App\Helpers;

class csv {

    protected $lines = [];

    // database field name => permissible csv field names
    private $fields = [
        'institution_code' => ['institution_code'],
        'object_number' => ['object_number'],
        'title' => ['title'],
        'object_category' => ['object_category'],
        'description' => ['description'],
        'reproduction_reference' => ['reproduction.reference', 'reproduction_reference'],
    ];

    // database field name => required
    private $required = [
        'institution_code' => false,
        'object_number' => true,
        'title' => true,
        'object_category' => true,
        'description' => false,
        'reproduction_reference' => true,
    ];

    protected $dbfields = [];

    protected $errors = [];

    /**
     * Supply csv in json format (from web service)
     * Convert to arrays
     * @param string $jsondata
     */
    public function process($jsondata) {
        if (!$this->lines = json_decode($jsondata)) {
            $error = json_last_error_msg();
            return $error;
        } else {
            return true;
        }
    }

    /**
     * Given text in header field return database field name
     * @param string $header
     * @return string
     */
    private function getDbField($header) {
        foreach ($this->fields as $field => $options) {
            foreach ($options as $option) {
                if ($option == $header) {
                    return $field;
                }
            }
        } 

        return null;
    }

    /**
     * Verify and extract header line
     * Assumed to be line 0
     */
    public function verifyHeaders() {
        $headers = $this->lines[0];
        $dbfields = [];
        foreach ($headers as $header) {
            $dbfields[] = $this->getDbField($header);
        }
        $this->dbfields = $dbfields;

        return $dbfields;
    }

    /**
     * Make sure that the object number has the correct digits
     * @param string $value
     * @return string
     */
    protected function fixObjectNumber($value) {
        $split = explode('.', $value);
        if (array_key_exists(1, $split)) {
            $split[1] = str_pad($split[1], 4, '0');
        }

        return implode('.', $split);
    }

    /**
     * Process line
     * Return db field against value
     * @param array $line
     * @param int offset
     * @return array
     */
    public function processLine($line, $offset) {
        $dbline = [];
        $error = false;
        foreach ($this->dbfields as $id => $dbfield) {
            if (array_key_exists($id, $line)) {
                $value = $line[$id];
                if ($dbfield == 'object_number') {
                    $value = $this->fixObjectNumber($value);
                }
            } else {
                $value = '';
            }
            if ($this->required[$dbfield] && empty($value)) {
                $this->errors[] = [
                    'offset' => $offset,
                    'dbfield' => $dbfield,
                    'error' => 'Required',
                ];
                $error = true;
            }
            $dbline[$dbfield] = $value;

            // add any missing field
            foreach ($this->fields as $fieldname => $junk) {
                if (!array_key_exists($fieldname, $dbline)) {
                    $dbline[$fieldname] = '';
                }
            }
        }
        $dbline['error'] = $error;

        return $dbline;
    }

    /**
     * Return a single line from the data
     * @param int $offset
     * @return array
     */
    public function getLine($offset) {
        if (array_key_exists($offset, $this->lines)) {
            $line = $this->lines[$offset];
        } else {
            $line = null;
        }

        return $line;
    }

    /**
     * Process all lines
     * @return array
     */
    public function processLines() {
        $processedlines = [];
        foreach ($this->lines as $id => $line) {
            if ($id == 0) {
                continue;
            }
            $processedline = $this->processLine($line, $id);
            $processedlines[] = $processedline;
        }

        return $processedlines;
    }

    /**
     * Get errors
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

} 
