# Botamp plugin for WordPress

[![Build Status](https://travis-ci.org/botamp/botamp-wordpress.svg?branch=master)](https://travis-ci.org/botamp/botamp-wordpress)

Botamp is an autonomous AI-enabled chat assistant which interacts seamlessly with your customers, keeps them engaged and makes them buy more from you, while you can focus on serving them.

You can sign up for a Botamp account at https://botamp.com.

The Botamp plugin for WordPress makes it easy to send content to your bot and your bot will use the content to respond to users.

It also makes it easy to enable order notifications for your customers so that they can receive
messages from your bot whenever their order status changes.

This plugin uses the [Botamp PHP SDK](https://github.com/botamp/botamp-php), to interact with the [Botamp API](https://app.botamp.com/docs/api).

## Requirements

PHP 5.6 and later (previous PHP versions may work but untested), HHVM.

## Settings
1. Enter your Botamp API key
2. Choose the post type your bot will use to respond to your customers
3. Choose the post fields your bot will use to respond to your customers

![Scrrenshot](settings.png)

## Features

### Create content
You create content for your bot just by creating a post.

### Update content
The content you created for you bot is automatically updated whenever the corresponding post is updated.

### Delete content
The content you created for your bot is automatically deleted whenever the corresponding post is deleted.

### Import all of your products
By clicking on the **Import all posts** button, you can import all of the existing posts into your bot.

### Order notifications
By enabling order notifications, your customers can opt-in to receive messages from your bot whenever their order status changes.
