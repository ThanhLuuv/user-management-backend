// pipeline {
//     agent any

//     environment {
//         APP_PORT = "8000"
//         DOCKER_IMAGE_NAME = "lvthanhwork/backend-laravel"
//         DOCKER_IMAGE_TAG = "backend"
//     }

//     stages {
//         stage('Build Docker Image') {
//             steps {
//                 script {
//                     dockerImage = docker.build("${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG}")
//                 }
//             }
//         }

//         stage('Push Docker Image') {
//             steps {
//                 script {
//                     docker.withRegistry('https://index.docker.io/v1/', 'dockerhub-credentials') {
//                         dockerImage.push()
//                     }
//                 }
//             }
//         }

//         stage('Trigger Render Deploy') {
//             steps {
//                 withCredentials([string(
//                     credentialsId: 'render-api-key',
//                     variable: 'RENDER_KEY'
//                 )]) {
//                     sh "curl -X POST 'https://api.render.com/deploy/srv-xxxxxxxxxxxxx?key=\$RENDER_KEY'"
//                 }
//             }
//         }
//     }

//     post {
//         always {
//             script {
//                 if (dockerImage) {
//                     sh "docker rmi ${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG} || true"
//                 }
//             }
//         }
//     }
// }
pipeline {
    agent any

    environment {
        IMAGE_NAME = 'lvthanhwork/backend-laravel'
        DOCKER_HUB_CREDENTIALS = credentials('docker-hub-creds')
        RENDER_DEPLOY_HOOK = credentials('render-api-key')
    }

    stages {
        stage('Clone code') {
            steps {
                git branch: 'master', url: 'https://github.com/ThanhLuuv/user-management-backend'
            }
        }

        stage('Build Docker Image') {
            steps {
                script {
                    sh 'docker build -t $IMAGE_NAME .'
                }
            }
        }

        stage('Login DockerHub') {
            steps {
                script {
                    sh "echo ${DOCKER_HUB_CREDENTIALS_PSW} | docker login -u ${DOCKER_HUB_CREDENTIALS_USR} --password-stdin"
                }
            }
        }

        stage('Push Image') {
            steps {
                sh 'docker push $IMAGE_NAME'
            }
        }

        stage('Trigger Render Deploy') {
            steps {
                script {
                    sh "curl -X GET 'https://api.render.com/deploy/${RENDER_DEPLOY_HOOK}'"
                }
            }
        }
    }
}
