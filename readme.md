# Fontanel Tumblr Importer

Fontanel Tumblr Importer is a small WordPress plugin that periodically imports the latest post from a tumblr blog. At the moment it's still very rough but as it's going to be part of the new Fontanel Magazine, it is likely to improve very soon.

## Installation

Add the source to your WordPress plugins folder, and activate it from the WordPress admin as you would with any plugin. **After activating the plugin you have to add the blog url and a Tumblr API key in the settings menu.** If you don't have an API key jet you can get one [here](http://www.tumblr.com/oauth/apps).

Also note that the plugin uses WordPress' scheduling hook [wp_schedule_event](http://codex.wordpress.org/Function_Reference/wp_schedule_event) which doesn't fire unless a visitor comes to your blog. The plugin currently tries to fetch the newest Tumblr post every five minutes, but only if someone visits your website within these five minutes. If this is a problem for you, you could set up a cronjob to call your sites' url every five minutes.

We like feedback, please [create an issue](https://github.com/jasperkennis/fontanel-tumblr-importer/issues) if you have an idea or need help.