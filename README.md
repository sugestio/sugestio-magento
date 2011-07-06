# Overview

This library integrates the [Sugestio](http://www.sugestio.com) recommendation service into 
[Magento](http://www.magentocommerce.com). Customers get personal recommendations and similar 
products based on purchase data or user reviews.

## About Sugestio

Sugestio is a scalable and fault tolerant service that now brings the power of web 
personalisation to all developers. The RESTful web service provides an easy to use 
interface and a set of developer libraries that enable you to enrich 
your content portals, e-commerce sites and other content-based websites.

### Access credentials and the Sandbox

To access the Sugestio service, you need an account name and a secret key. 
To run the examples from the tutorial, you can use the following credentials:

* account name: <code>sandbox</code>
* secret key: <code>demo</code>

The Sandbox is a *read-only* account. You can use these credentials to experiment 
with the service. The Sandbox can give personal recommendations for users 1 through 5, 
and similar items for items 1 through 5.

When you are ready to work with real data, you may apply for a developer account through 
the [Sugestio website](http://www.sugestio.com).

## About this module

### Features

The following [API](http://www.sugestio.com/documentation) features are fully implemented:

* get personalized recommendations for a given user
* get products that are similar to the currently viewed product
* purchase data, customer reviews and product metadata (categories and tags) are automatically synchronized

### Requirements

This module depends on [sugestio-php](http://github.com/sugestio/sugestio-php) for communicating 
with the webservice. A copy of this library is already included in the /lib subdirectory. 

### Installation

1. The installation of this module is very straightforward. Simply upload the entire app directory to the root folder of your Magento installation. 
1. Clear the magento cache through the administration panel (System => Cache Management => Flush Magento Cache and Flush Cache Storage). 
1. The module **Sugestio_Recommender** should now be visible in the modules table. (System => Configuration => Advanced => Advanced) 
1. You've probably noticed that there's a new Configuration Area named Sugestio in the Configuration panel (System => Configuration). There you can configure the Sugestio module, for now just enter your account name and key. If the Sugestio Configuration Page doesn't show a form or it gives a HTTP 404 error, make sure you've cleared your cache as in step 2.
1. From now on you should see two new blocks on your magento sites:
    * Recommended Products: personal recommendations for the current user are in the left sidebar of most pages (except the product page and the homepage).
    * Similar Products: products that are similar to the currently viewed product are in the right sidebar of the product pages.

### Configuration

You can adjust the Sugestio module in the administration panel 
(System => Configuration => Sugestio Configuration). The configuration options are:

* **Url of the Sugestio API** - Which Sugestio endpoint to use. Defaults to http://api.sugestio.com
* **Accountname** - Your sugestio account name.
* **Key** - Your Sugestio account secret.
* **Number items** - The number of recommended/similar products that should be fetched. This affects both the number of recommended/similar items that will be fetched from the web service as the number of items that will be shown in the standard Sugestio Recommended Items block.
* **Enable caching** - Enables the cache for the recommendations. Recommendations will be fetched from cache first before calling the external Sugestio recommendation engine.
* **Cache expire time** - The maximum time (in hours) the recommendations can be stored in the cache. After this period the recommendations will be refetched from the web service.
* **Send realtime hook events** - Allows the hook events (e.g. new purchases, new reviews) to be submited in real-time. If disabled, these events will be stored in (non-expiring) cache to be executed when cron is run. This can be disabled for performance reasons.

The first three parameters are required in order for the module to be able to connect to the Sugestio service.
We refer to the following sections for a more elaborate discussion about the cache mechanism, hook events and click tracking.

### Customizing the layout

You can adjust the layout of the Sugestio blocks so they fit your store's design by editing 
/app/design/frontend/default/default/layout/sugestio.xml. For more information about adjusting the layout read the [Magento designer's guide](http://www.magentocommerce.com/design_guide/), 
more specifically [this chapter](http://www.magentocommerce.com/design_guide/articles/intro-to-layouts).
Also note that the template and layout of the Sugestio module are only installed at the 
default design, if you use other designs then you should also copy (and maybe adjust) the 
layout and templates to those designs, located in /app/design/frontend/default/.

### Module implementation details
Developers are free to extend this module so that it fits their own custom use cases. 
This section describes how the Sugestio Magento module works, how it is structured and where 
possible extentions can be added.

#### Getting recommendations
This is what it's all about: getting recommendations from the Sugestio web service and displaying 
them on your Magento website. For Sugestio to be able to produce relevant recommendations, 
data must be sent to the service. This data is gathered by means of several hooks in the module. 
For the more information about the hooks and how data is sent to the service we refer to the 
next section. For now we assume that Sugestio has sufficient data and that recommendations are 
ready to be retrieved.

Two types of recommendations exist: recommended products and similar products. 
The recommended products are recommended specifically to a particular user based on their actions
on the site (purchases, reviews, ...), while the similar products are products that are typically
bought by the same type of user.

Because recommendations typically don't change very often, the module allows the recommendations 
to be cached. This way fetching the recommendations for the same user twice in a 
short period of time will only invoke one call to the web service. The second time, 
recommendations can be fetched from cache instead. This only applies if the **enable cache** 
option is enabled in the Sugestio configuration panel.

#### Hooks

The Sugestio module hooks onto several events triggered by the Magento core modules. 
These triggers will invoke a method from the Observer class. (/app/code/local/Sugestio/Recommender/Model/Observer.php) 
in order to collect certain data and dispatch a call to the Sugestio web service. 
If you disabled a core module, then the events from that module won't be triggered and there 
will be no call to the web service. The events that the Sugestio module hooks onto are:

* **catalog_product_save_after** - product information updated (also happens after adding a new product).
* **customer_save_after** - customer information updated (also happens after a registration).
* **catalog_controller_product_view** - product viewed.
* **wishlist_add_product** - a customer has added a product to their wishlist.
* **checkout_cart_add_product_complete** - product added to basket.
* **checkout_onepage_controller_success_action** - product purchased (onepage).
* **checkout_type_multishipping_create_orders_single** - product purchased (multishipping).
* **rating_vote_added** - in the current version (1,4,1) the core Rating module doesn't dispatch an event when saving a rating of a review. Therefor, we had to overwrite the function addVote of Mage_Rating_Model_Rating_Option. This dispatches the event rating_vote_added after saving a rating.
* **catalog_product_compare_add_product** - product added to compare list.

#### Cron

This module can use the cron features of Magento to execute calls to the Sugestio web service. 
If a call doesn't succeed, the call will then be stored in the database (table sugestio_cron, 
created automatically when enabling the module). When the cron is run, one by one every queued 
call is executed and removed from the database if succesful. If the option **realtime hook events** 
is disabled, calls are always placed into the cron table for asynchronous execution.