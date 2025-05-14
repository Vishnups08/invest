pipeline {
    agent any

    environment {
        IMAGE_NAME = 'invest'
        PATH = "/opt/homebrew/bin:/usr/local/bin:/usr/bin:/bin:/usr/sbin:/sbin:${env.PATH}"
        DOCKERHUB_CREDENTIALS = credentials('dockerhub-creds')
    }
    
    stages {
        stage('Clone Repo') {
            steps {
                git branch: 'main', url: 'https://github.com/Vishnups08/invest.git'
            }
        }

        stage('Build Docker Image') {
            steps {
                sh 'docker build -t ${IMAGE_NAME} .'
            }
        }

        stage('Test') {
            steps {
                echo 'No tests implemented yet'
            }
        }

        stage('Push to DockerHub') {
            steps {
                sh 'echo $DOCKERHUB_CREDENTIALS_PSW | docker login -u $DOCKERHUB_CREDENTIALS_USR --password-stdin'
                sh 'docker tag ${IMAGE_NAME} ${DOCKERHUB_CREDENTIALS_USR}/${IMAGE_NAME}:latest'
                sh 'docker push ${DOCKERHUB_CREDENTIALS_USR}/${IMAGE_NAME}:latest'
                sh 'docker logout'
            }
        }

        stage('Deploy') {
            steps {
                echo 'Triggering Render deployment...'
                sh 'curl -X POST https://api.render.com/deploy/srv-d0g5n0buibrs73f9gimg?key=mJxztRPfAHU'
            }
        }
    }
}

