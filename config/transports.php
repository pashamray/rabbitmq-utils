<?php

use Symfony\Component\Yaml\Yaml;

return Yaml::parseFile(realpath('transports.yaml'));
