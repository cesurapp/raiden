import fs from 'fs'
import path from 'path'

export default function firebasePublicGenerator() {
  const templatePath = path.resolve(__dirname, 'firebase-messaging-sw.js')
  const outputPath = path.resolve('public/firebase-messaging-sw.js')

  function generate(env) {
    let content = fs.readFileSync(templatePath, 'utf8')

    content = content
      .replace('<FIREBASE_APIKEY>', process.env.FIREBASE_APIKEY || '')
      .replace('<FIREBASE_DOMAIN>', process.env.FIREBASE_DOMAIN || '')
      .replace('<FIREBASE_PROJECTID>', process.env.FIREBASE_PROJECTID || '')
      .replace('<FIREBASE_STORAGEBUCKET>', process.env.FIREBASE_STORAGEBUCKET || '')
      .replace('<FIREBASE_SENDERID>', process.env.FIREBASE_SENDERID || '')
      .replace('<FIREBASE_APPID>', process.env.FIREBASE_APPID || '')

    fs.writeFileSync(outputPath, content)
    console.log('✅ public/firebase-messaging-sw.js generated')
  }

  return {
    name: 'vite-plugin-generate-firebase-sw',

    configResolved(config) {
      generate(config.env)
    },

    handleHotUpdate({ file, server }) {
      if (file.includes('.env')) {
        generate(server.config.env)
      }
    }
  }
}
