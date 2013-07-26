PHPUnit Coverage Listener
=========================
[![Build Status](https://travis-ci.org/php-loep/phpunit-coverage-listener.png)](https://travis-ci.org/php-loep/phpunit-coverage-listener) [![Dependencies Status](https://d2xishtp1ojlk0.cloudfront.net/d/11688670)](http://depending.in/php-loep/phpunit-coverage-listener) [![Coverage Status](https://coveralls.io/repos/php-loep/phpunit-coverage-listener/badge.png?branch=master)](https://coveralls.io/r/php-loep/phpunit-coverage-listener?branch=master)

PHPUnit Coverage Listener is a utility library that allow you to process the PHPUnit code-coverage information and send it into some remote location via cURL. It could be used, for example, to send the payload data for [Coveralls](https://coveralls.io/) effortless from your composer-based project.

Install
-------

Via Composer

    {
        "require": {
            "league/phpunit-coverage-listener": "~1.0"
        }
    }
    
Requirement
-----------

* PHP >= 5.3.3

Basic Usage
-----------

Let's say you want to send a payload data for [Coveralls](https://coveralls.io/) each time your [Travis](http://travis-ci.org/) job successfully build. All you need is adding bellow section within your phpunit configuration :
	
	<logging>
        <log type="coverage-clover" target="/tmp/coverage.xml"/>
    </logging>
    <listeners>
        <listener class="League\PHPUnitCoverageListener\Listener">
            <arguments>
                <array>
                    <element key="printer">
                      <object class="League\PHPUnitCoverageListener\Printer\StdOut"/>
                    </element>
                    <element key="hook">
                      <object class="League\PHPUnitCoverageListener\Hook\Travis"/>
                    </element>
                    <element key="namespace">
                        <string>League\PHPUnitCoverageListener</string>
                    </element>
                    <element key="repo_token">
                        <string>YourCoverallsRepoToken</string>
                    </element>
                    <element key="target_url">
                        <string>https://coveralls.io/api/v1/jobs</string>
                    </element>
                    <element key="coverage_dir">
                        <string>/tmp</string>
                    </element>
                </array>
            </arguments>
        </listener>
    </listeners>

And thats it.

Advance Usage
-------------

As you may noticed on previous section, in order to work properly, Listener class need to know several things. They are being passed from `phpunit.xml` file.

Bellow table describe each configuration respectively : 

| Key Name | Value | Description
| :---: | :---: | :---: |
| `printer` | `League\PHPUnitCoverageListener\PrinterInterface` | Required |
| `hook` | `League\PHPUnitCoverageListener\HookInterface` | Optional |
| `namespace` | `String` | Optional |
| `repo_token` | `String` | Required |
| `target_url` | `String` | Required |
| `coverage_dir` | `String` | Required |


Changelog
---------

Contributing
------------

Please see [CONTRIBUTING](https://github.com/php-loep/phpunit-coverage-listener/blob/master/CONTRIBUTING.md) for details.

Support
-------

Bugs and feature request are tracked on [GitHub](https://github.com/php-loep/phpunit-coverage-listener/issues)

License
-------

PHPUnit Coverage Listener is released under the MIT License. See the bundled
[LICENSE](https://github.com/php-loep/phpunit-coverage-listener/blob/master/LICENSE) file for details.