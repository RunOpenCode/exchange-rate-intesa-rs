node {
    stage ('Provisioning') {
        git 'https://github.com/RunOpenCode/exchange-rate-intesa-rs'
    }
    docker.image('runopencode/php-testing-environment:7.0.15').inside {
        stage ('Build on 7.0.15') {
            sh 'ant'
        }
    }
    docker.image('runopencode/php-testing-environment:7.1.1').inside {
        stage ('Build on 7.1.1') {
            sh 'ant'
        }
    }
    stage('SonarQube') {
        def scannerHome = tool 'SonarQube Scanner 2.8';
        withSonarQubeEnv {
            sh "${scannerHome}/bin/sonar-scanner -Dsonar.projectKey=runopencode-exchange-rate-banca-intesa-rs -Dsonar.projectName='Exchange rate - Banca Intesa Serbia adapter' -Dsonar.projectVersion=1.0 -Dsonar.sources=src -Dsonar.language=php -Dsonar.sourceEncoding=UTF-8 -Dsonar.tests=test -Dsonar.php.tests.reportPath=build/logs/junit.xml -Dsonar.php.coverage.reportPath=build/logs/clover.xml -Dsonar.clover.reportPath=build/logs/clover.xml -Dsonar.coverage.exclusions=test"
        }
    }
}