pipeline {
  agent any
  stages {
    stage('Checkout') {
      steps {
        git(url: 'https://github.com/Net-Logic/dolibarr_module_prune.git', branch: 'main')
      }
    }
    stage('Test') {
      parallel {
        stage('PHP 7.2') {
          agent {
            docker {
              image 'php:7.2-cli'
              args '-u root:sudo'
            }
          }
          steps {
            echo 'Running PHP 7.2 tests...'
            sh 'php -v'
            echo 'Installing Composer'
            sh 'curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer'
            echo 'Installing project composer dependencies...'
            sh 'cd $WORKSPACE && composer install --no-progress'
            echo 'Running PHP Parallel-Lint tests...'
            //sh 'php $WORKSPACE/vendor/bin/phpunit --coverage-html $WORKSPACE/report/clover --coverage-clover $WORKSPACE/report/clover.xml --log-junit $WORKSPACE/report/junit.xml'
            //sh 'php $WORKSPACE/vendor/bin/parallel-lint --checkstyle --exclude $WORKSPACE/vendor/ . > $WORKSPACE/build/logs/pll-checkstyle.xml'
            sh 'php $WORKSPACE/vendor/bin/parallel-lint --exclude vendor/ .'
            echo 'Running PHPcs tests...'
            sh 'php $WORKSPACE/vendor/bin/phpcs --report=checkstyle --report-file=build/logs/checkstyle.xml -s -p --standard=codesniffer/ruleset.xml --colors --extensions=php,inc --ignore=autoload.php --ignore=vendor/ --runtime-set ignore_warnings_on_exit true .'
            echo 'Running check remaining debug...'
            sh 'php $WORKSPACE/vendor/bin/var-dump-check --extensions php --tracy --exclude vendor/ .'
            sh 'chmod -R a+w $PWD && chmod -R a+w $WORKSPACE'
            //junit 'report/*.xml'
          }
        }
        stage('PHP 7.3') {
          agent {
            docker {
              image 'php:7.3-cli'
              args '-u root:sudo'
            }

          }
          steps {
            echo 'Running PHP 7.3 tests...'
            sh 'php -v'
            echo 'Installing Composer'
            sh 'curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer'
            echo 'Installing project composer dependencies...'
            sh 'cd $WORKSPACE && composer install --no-progress'
            echo 'Running PHP Parallel-Lint tests...'
            //sh 'php $WORKSPACE/vendor/bin/phpunit --coverage-html $WORKSPACE/report/clover --coverage-clover $WORKSPACE/report/clover.xml --log-junit $WORKSPACE/report/junit.xml'
            //sh 'php $WORKSPACE/vendor/bin/parallel-lint --checkstyle --exclude $WORKSPACE/vendor/ . > $WORKSPACE/build/logs/pll-checkstyle.xml'
            sh 'php $WORKSPACE/vendor/bin/parallel-lint --exclude vendor/ .'
            echo 'Running PHPcs tests...'
            sh 'php $WORKSPACE/vendor/bin/phpcs --report=checkstyle --report-file=build/logs/checkstyle.xml -s -p --standard=codesniffer/ruleset.xml --colors --extensions=php,inc --ignore=autoload.php --ignore=vendor/ --runtime-set ignore_warnings_on_exit true .'
            sh 'chmod -R a+w $PWD && chmod -R a+w $WORKSPACE'
            //junit 'report/*.xml'
          }
        }
        stage('PHP 7.4') {
          agent {
            docker {
              image 'php:7.4-cli'
              args '-u root:sudo'
            }
          }
          steps {
            echo 'Running PHP 7.4 tests...'
            sh 'php -v'
            echo 'Installing Composer'
            sh 'curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer'
            echo 'Installing project composer dependencies...'
            sh 'cd $WORKSPACE && composer install --no-progress'
            echo 'Running PHP Parallel-Lint tests...'
            //sh 'php $WORKSPACE/vendor/bin/phpunit --coverage-html $WORKSPACE/report/clover --coverage-clover $WORKSPACE/report/clover.xml --log-junit $WORKSPACE/report/junit.xml'
            //sh 'php $WORKSPACE/vendor/bin/parallel-lint --checkstyle --exclude $WORKSPACE/vendor/ . > $WORKSPACE/build/logs/pll-checkstyle.xml'
            sh 'php $WORKSPACE/vendor/bin/parallel-lint --exclude vendor/ .'
            echo 'Running PHPcs tests...'
            sh 'php $WORKSPACE/vendor/bin/phpcs --report=checkstyle --report-file=build/logs/checkstyle.xml -s -p --standard=codesniffer/ruleset.xml --colors --extensions=php,inc --ignore=autoload.php --ignore=vendor/ --runtime-set ignore_warnings_on_exit true .'
            sh 'chmod -R a+w $PWD && chmod -R a+w $WORKSPACE'
            //junit 'report/*.xml'
          }
        }

      }
    }
    stage('Release') {
      steps {
        echo 'Ready to release etc.'
      }
    }
  }
}