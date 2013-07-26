PHPUnit Coverage Listener
=========================
[![Build Status](https://travis-ci.org/php-loep/phpunit-coverage-listener.png)](https://travis-ci.org/php-loep/phpunit-coverage-listener) [![Dependencies Status](https://d2xishtp1ojlk0.cloudfront.net/d/11688670)](http://depending.in/php-loep/phpunit-coverage-listener) [![Coverage Status](https://coveralls.io/repos/php-loep/phpunit-coverage-listener/badge.png?branch=master)](https://coveralls.io/r/php-loep/phpunit-coverage-listener?branch=master)

PHPUnit Coverage Listener is a utility library that allow you to process the PHPUnit code-coverage information and send it into some remote location via cURL. It could be used, for example, to effortlessly send the payload data for [Coveralls](https://coveralls.io/) from your composer-based project Continuous-Integration server.

Requirement
-----------

* PHP >= 5.3.3

Install
-------

Via Composer

    {
        "require": {
            "league/phpunit-coverage-listener": "~1.0"
        }
    }
    

Basic Usage
-----------

Let's say you want to send a payload data for [Coveralls](https://coveralls.io/) each time your [Travis](http://travis-ci.org/) job successfully build. All you need is to adding bellow section within your phpunit configuration :
	
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

As you may noticed on previous section, in order to work properly, Listener class need to know several things. They are being passed from your phpunit configuration within listener arguments directive.

Bellow table describe each configuration options respectively : 

| Key Name | Value | Description
| :---: | :---: | :---: |
| `printer` | `League\PHPUnitCoverageListener\PrinterInterface` | Required |
| `hook` | `League\PHPUnitCoverageListener\HookInterface` | Optional |
| `namespace` | `String` | Optional |
| `repo_token` | `String` | Required |
| `target_url` | `String` | Required |
| `coverage_dir` | `String` | Required |

### printer

This option contains `PrinterInterface` that will be used by Listener class in several points. In previous section, we set it to use `StdOut` printer that will print out any output informations directly into standard output. You could use your own printer class as long as it implements required interface.

### hook

This option allow you to hook into Listener life-cycle. `HookInterface` has two method to be implemented : `beforeCollect` and `afterCollect`. It will receive `Collection` data, and then will alter or do something with the data on each hook point. In the previous example, `Travis` hook actually only contains bellow code :

    public function beforeCollect(Collection $data)
    {
        // Check for Travis-CI environment
        // if it appears, then assign it respectively
        if (getenv('TRAVIS_JOB_ID') || isset($_ENV['TRAVIS_JOB_ID'])) {
            // Remove repo token
            $data->remove('repo_token');

            // And use travis config
            $travis_job_id = isset($_ENV['TRAVIS_JOB_ID']) ? $_ENV['TRAVIS_JOB_ID'] : getenv('TRAVIS_JOB_ID');
            $data->set('service_name', 'travis-ci');
            $data->set('service_job_id', $travis_job_id);
        }

        return $data;
    }

You could register your own hook class that suit for your need as long as it implements required interface.

### namespace

Option `namespace` string could be passed into the Listener, so that the generated coverage information use "relative" name instead literal file path. For example, if your source is `src/My/Package/Resource.php`, and you passing `My\Package` as namespace option, generated file name within coverage payload data will be `My/Package/Resource.php`.

### repo_token

This option could be anything. Timestamp? Coveralls account token? Jenkins build token? Its up to you. But it was still neccessary to supply this option into the Listener class.

### target_url

This option could be any valid url. For example, if you use Coveralls this option can be set to its REST endpoint : `https://coveralls.io/api/v1/jobs`.

### coverage_dir

The directory you specified here **must** be the same directory from which PHPUnit generate `coverage.xml` report. Listener will also outputing `coverage.json` within this directory, so ensure this directory is writable.

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