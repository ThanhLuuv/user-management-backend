pipeline {
    agent any

    environment {
        APP_PORT = "8000"
        DOCKER_IMAGE_NAME = "lvthanhwork/backend-laravel"
        DOCKER_IMAGE_TAG = "backend"
    }

    stages {
        stage('Build Docker Image') {
            steps {
                script {
                    dockerImage = docker.build("${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG}")
                }
            }
        }

        stage('Push Docker Image') {
            steps {
                script {
                    docker.withRegistry('https://index.docker.io/v1/', 'dockerhub-credentials') {
                        dockerImage.push()
                    }
                }
            }
        }

        stage('Trigger Render Deploy') {
            steps {
                withCredentials([string(
                    credentialsId: 'render-api-key',
                    variable: 'RENDER_KEY'
                )]) {
                    sh "curl -X POST 'https://api.render.com/deploy/srv-xxxxxxxxxxxxx?key=\$RENDER_KEY'"
                }
            }
        }
    }

    post {
        always {
            script {
                if (dockerImage) {
                    sh "docker rmi ${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG} || true"
                }
            }
        }
    }
}
