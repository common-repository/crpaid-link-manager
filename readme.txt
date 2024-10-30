=== [CR]Paid Link Manager ===
Contributors: silentwind
Donate link: http://bayu.freelancer.web.id/about/
Tags: paid link, widget, custom table, sidebar
Requires at least: 3.0
Tested up to: 3.1
Stable tag: 0.5

A plugin that will help you manage your paid link's life cycle

== Description ==

Do you sell your blogroll and having problem in keeping an updated information as to when this particular link is expired? You will no longer be in trouble with this plugin. Introducing: [CR]Paid Link Manager. A plugin that will help you manage your paid link's life cycle.

This is what current version can do:

*   The ability to create a separate group so that you can further set each group belong to what condition. I'll explain this later.
*   The ability to set per link age. So that it will automatically hide link that's expired.
*   The ability to store per link notes. It is useful for storing link detail, like client info (email subject reference maybe), so that you can remind him that their link is expiring.
*   Add support for infinite age link (partner link or your other sites)
*   Automatic email reminder for expiring links (sent out daily)

With planned (not so distant) future upgrade:

*   Plugin API, so that other plugin or theme developer can use it seamlessly without too much hack
*   Add javascript for link age input. I know you will love this
*   Add ability to specify different separator for 'coma' mode


== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `cr-paidlinkmanager.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Read detailed how to use [on my blog](http://bayu.freelancer.web.id/oss/crpaid-link-manager-plugin-to-manage-your-paid-links-life-cycle/ "on my blog")

== Frequently Asked Questions ==

= Why is this section empty? =

Because I don't have anything so far. Will update once there is.

== Screenshots ==

1. Admin menu
2. Link Group management
3. Add New Link
4. Widget Configs. Notice the `Widget Logic`. It's from plugin called *Widget Logic*
5. Different display mode

== Changelog ==

= 0.4 =
*   ENHANCE: Show Link_ID and Group_ID for easy viewing.

= 0.4 =
*   BUGFIX: Prevent sending `expiring email` when no expiring link.

= 0.3 =
*   Introduce ability set certain link to never expired (set date2 to 0000-00-00)
*   Introduce ability to send expiring email daily

= 0.2 =
*   BUGFIX: Warning: call_user_func_array() [function.call-user-func-array]: First argument is expected to be a valid callback, 'cr_post_2_pingfm_init' was given in PATH/wp-includes/plugin.php on line 395

= 0.1 =
*   The ability to create a separate group so that you can further set each group belong to what condition. I'll explain this later.
*   The ability to set per link age. So that it will automatically hide link that's expired.
*   The ability to store per link notes. It is useful for storing link detail, like client info (email subject reference maybe), so that you can remind him that their link is expiring.
*   Introduce template functions `cr_paid_link_manager_show_links()`

== Open For Hire ==

Got awesome wordpress project? need something to be fixed? plugin to be made? You can contact me at ariefbayu@freelancer.web.id
Want to make a paid version of this plugin? let's discuss...
