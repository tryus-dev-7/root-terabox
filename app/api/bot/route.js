// pages/api/telegramBot.js
import axios from 'axios';
import fs from 'fs';
import path from 'path';

const TELEGRAM_BOT_TOKEN = process.env.TELEGRAM_BOT_TOKEN;
const ADMIN_CHAT_ID = process.env.ADMIN_CHAT_ID;
const API_ENDPOINT = process.env.API_ENDPOINT;

// Define the user data file path
const USER_DATA_FILE = path.join(process.cwd(), 'user_data.json');

// Load existing user data from the file if it exists
let userData = [];
if (fs.existsSync(USER_DATA_FILE)) {
  userData = JSON.parse(fs.readFileSync(USER_DATA_FILE));
}

// Helper function to save user data
function saveUserData() {
  fs.writeFileSync(USER_DATA_FILE, JSON.stringify(userData));
}

// Function to fetch download links from the API
async function fetchDownloadLinks(url) {
  try {
    const response = await axios.post(API_ENDPOINT, { url });
    const data = response.data.response || [];
    if (data.length > 0) {
      const { resolutions, title } = data[0];
      return {
        title,
        hd_video_link: resolutions["HD Video"],
        fast_download_link: resolutions["Fast Download"],
      };
    }
  } catch (error) {
    console.error("Error fetching download links:", error);
  }
  return null;
}

// Function to escape special characters for MarkdownV2
function escapeMarkdownV2(text) {
  return text.replace(/([_*[\]()~`>#+\-=|{}.!])/g, '\\$1');
}

// Main handler function for the webhook
export default async function handler(req, res) {
  if (req.method === 'POST') {
    const { message } = req.body;

    if (message && message.text) {
      const chatId = message.chat.id;
      const url = message.text.trim();
      const firstName = message.chat.first_name || "User";
      const userName = message.chat.username || "";

      // Add new user if not already in the list
      if (!userData.includes(chatId)) {
        userData.push(chatId);
        saveUserData();
        
        // Notify admin of new user
        const totalUsers = userData.length;
        await axios.post(`https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage`, {
          chat_id: ADMIN_CHAT_ID,
          text: `➡️ *New User Started The Bot :*\n🆔 User ID : ${chatId}\n👨🏻‍💻 Username : ${userName}\n🌐 Total Users : ${totalUsers}`,
          parse_mode: "MarkdownV2"
        });
      }

      // Notify user that video is being processed
      await axios.post(`https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage`, {
        chat_id: chatId,
        text: "*⚡ Generating video...*",
        parse_mode: 'Markdown'
      });

      // Fetch download links
      const downloadLinks = await fetchDownloadLinks(url);
      if (downloadLinks) {
        const { title, hd_video_link, fast_download_link } = downloadLinks;
        const escapedTitle = escapeMarkdownV2(title);

        // Define inline keyboard options
        const options = {
          inline_keyboard: [
            [{ text: "⬇️ Download Video", url: hd_video_link }],
            [{ text: "🚀 Download Video (Fast)", url: fast_download_link }]
          ]
        };

        // Send the response with download links
        await axios.post(`https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage`, {
          chat_id: chatId,
          text: `*➡️ Title :* ${escapedTitle}\n\n_Choose an option below:_`,
          reply_markup: { inline_keyboard: options.inline_keyboard },
          parse_mode: 'MarkdownV2'
        });
      } else {
        // Notify user if the URL is invalid
        await axios.post(`https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage`, {
          chat_id: chatId,
          text: "*⚠️ Invalid URL*\n\n_Please check the URL and try again._",
          parse_mode: 'Markdown'
        });
      }
    }
    res.status(200).send("Webhook received");
  } else {
    res.status(405).send("Method Not Allowed");
  }
}
