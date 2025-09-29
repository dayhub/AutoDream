// config/config.js
require('dotenv').config();

module.exports = {
  PORT: process.env.PORT || 5000,
  MONGODB_URI: process.env.MONGODB_URI || 'mongodb://localhost:27017/automarket',
  JWT_SECRET: process.env.JWT_SECRET || 'your-secret-key',
  UPLOAD_PATH: process.env.UPLOAD_PATH || 'uploads/',
  
  // Cloud storage configuration (optional)
  CLOUD_STORAGE: {
    provider: process.env.CLOUD_STORAGE_PROVIDER, // 'aws', 'google', 'azure'
    bucket: process.env.CLOUD_STORAGE_BUCKET,
    region: process.env.CLOUD_STORAGE_REGION
  }
};