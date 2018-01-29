=== CPO Content Types ===
Contributors: cpothemes
Tags: portfolio, features, slider, testimonials, clients, team, team members, services
Requires at least: 4.0
Tested up to: 4.5
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add support for special content types in your website, such as a portfolio, features, and slides.

== Description ==

**NOTE: This plugin is meant for use with the WordPress themes developed by [CPOThemes](http://cpothemes.com) themes, which take advantage of it to add richer content areas and designs. Check them out!** 

[CPO Content Types](http://cpothemes.com/plugin/cpo-content-types) is a utility plugin that adds support for a specific set of content elements within your WordPress installation. This plugin will add seven custom post types to your site: slides, features, portfolios, services, team members, testimonials and clients. You can still use CPO Content Types for any WordPress theme, although you will have to create your own page templates.

= Included Content Types =
* Slides
* Feature Blocks
* Portfolio Items
* Services
* Team Members
* Testimonials
* Clients

= Highlights =

* Only the content types supported by the current WordPress theme will be shown, to avoid crowding your admin menu. You can still override this and show any content types if you want.
* This plugin is perfectly compatible with any theme: you will be able to manage your content just fine. However, there are no templates included and it is up to the theme to handle them.
* The portfolio post type included here is different from other portfolio plugins, and can be used in conjunction with them. For instance, you can still use JetPack portfolios at the same time.

== Installation ==

= Installing Through The WordPress Admin =

1. Download the ZIP file
2. In your WordPress admin area, go to Plugins > Add Plugin, and select Upload Plugin
3. Upload the ZIP file and installation will commence
4. Activate the plugin through the 'Plugins' menu in WordPress
5. Make sure to check the **Settings > CPO Content Types** page before using it right away!
6. You are now ready to use the new content types in the WordPress Admin

= Installing Through FTP =

1. Download the ZIP file and unpack it
2. Upload the entire **cpo-content-types** to the **/wp-content/plugins/** directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Make sure to check the **Settings > CPO Content Types** page before using it right away!
5. You are now ready to use the new content types in the WordPress Admin

== Frequently Asked Questions ==

[Visit Our Support Forum At CPOThemes](http://www.cpothemes.com/forums/)

= Introduction To CPO Content Types =
The CPO Content Types plugin is a free, lightweight utility plugin that is meant to go hand in hand with all our themes. To fully use our themes, you will need to install and activate this plugin.
Why do I need CPO Content Types?

Since a theme is only meant to present information, all extra functionality is separated and moved to an external plugin. This way, you will have access to all you information even if you decide to switch themes.

In short, CPO content Types will enable multiple types of content that can be used in our themes to create rich sites. As of now, there are up to seven content types supported:
Slides

Used for creating rotating homepage sliders. Slides are usually accompanied by a background image and a few options that let you configure its appearance.
Feature blocks

Icon blocks normally used to present small chunks of information, accompanied by an icon and an image.
Portfolio Items

The portfolio can be used as a highly visual type of content. It is great for presenting works, projects, clients, or other type of media. This content type is public, and each post you create under this section will have its own URL.

Portfolio items can also be categorized in portfolio categories and portfolio tags.
Services

In addition to portfolios, services can also be used as a less visual content type to showcase offering, custom services, or other product lists. Like portfolios, each service also has its own URL.

Services can also be categorized in service categories and service tags.
Testimonials

You can use testimonials to add social proof to your website, and to boost credibility.
Team members

Team members can represent individual people within your organization, or speakers in an event. A member will have a title, image, text, as well as links to different social networks.

Team members can be categorize in diffrent member groups, which you can then use to display separate collections of people across different pages.
Clients

You can use the clients content type to showcase different logos throughout your site.
Support for content types

Not all our themes make use of every content type available. For example, portfolio-oriented themes will typically use the slide, feature, and portfolio content types.

In cases where a theme does not support all content types, they will be hidden from view to avoid cluttering up the admin interface. If you want to manage them, you can go to Settings > CPO Content Types and override this behavior by selecting individual content types.
Changing the URL of your content types

The portfolio, services, and team member content types all have public-facing pages with their own URL. By default, they will be displayed using a predetermined slug. Thus, if you have created a portfolio item named Redesign, you may end up with something like the following:

www.mysite.com/portfolio-item/redesign

If you are using the portfolio content type for projects, it is not optimal to display the portfolio-item slug. You can change the slug of all public-facing content in the Settings > CPO Content Types section, so that it becomes more consistent with your usage. This way, you can use the portfolio with URLs such as the following:

www.mysite.com/project/redesign


== Screenshots ==

1. Manage post types from the admin panel.

== Changelog ==

= 1.1.0 =
* Rewrite rules are now flushed on plugin activation and settings save.

= 1.0.5 =
* Capabilities adjusted to be in line with pages, not posts.

= 1.0.4 =
* Content types will now be displayed only if the current theme supports it.
* Added options to control the display of unsupported content types.

= 1.0.3 =
* Added comments support to portfolios
* Added settings to control the slug of team groups.

= 1.0.2 =
* Added support for testimonials, services, clients and team members

= 1.0.1 =
* Adjustments to post types

= 1.0.0 =
* Plugin release, yay!