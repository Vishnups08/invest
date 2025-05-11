pipeline {
    agent any

    environment {
        IMAGE_NAME = 'vishnups08/invest'
    }

    stages {
        stage('Clone Repo') {
            steps {
                git 'https://github.com/Vishnups08/invest.git'
            }
        }

        stage('Build Docker Image') {
            steps {
                script {
                    docker.build(IMAGE_NAME)
                }
            }
        }

        stage('Test') {
            steps {
                echo 'No tests implemented yet'
            }
        }

        stage('Push to DockerHub') {
            steps {
                withDockerRegistry([credentialsId: 'dockerhub-creds', url: '']) {
                    script {
                        docker.image(IMAGE_NAME).push('latest')
                    }
                }
            }
        }

        stage('Deploy') {
            steps {
                echo 'Triggering Render deployment...'
                sh 'curl -X POST https://api.render.com/deploy/srv-xxxxxxxxxx?key=xxxxxxxxxx'
    }
}
    }
}
