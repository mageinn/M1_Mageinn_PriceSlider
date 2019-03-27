Mageinn_PriceSlider
===================

http://mageinn.com/product/price-slider/

What Is This?
-------------

AJAX Layered Navigation & Toolbar with Multiselect & Price Slider

Mageinn Ajax Price Slider is a Magento module, which allows users to filter 
products by certain price range as per their choice. It uses noUiSlider which 
is a range slider without bloat. It offers a ton off features, and it is as small, 
lightweight and minimal as possible, which is great for mobile use on the many 
supported devices, including iPhone, iPad, Android devices & Windows (Phone) 8 
desktops, tablets and all-in-ones. It works on desktops too, of course!

Module Features:

- Lightweight

- Ajax powered

- Select multiple attributes & multiple attribute values at once

- Super easy to style

- Configurable switch to enable Ajax for Layered Navigation and Toolbar

- Mobile friendly

Price slider is completely mobile-friendly in relation to all Magento themes. 
It perfectly fits to responsive templates and meets the requirements of 
touch-screen devices.

- Configurable step size for price slider

Demo:
http://mageinn.com/sandbox/mobile-phones.html


How To Install The Module
-------------------------

1. Extract it and simply copy the files over to the Magento core

2. Refresh the cache

3. Enable the module in "System > Configuration -> MAGEINN -> Ajax Price Slider"

4. Enable jQuery in "System > Configuration -> MAGEINN -> General"
NOTE: This step is OPTIONAL, enable only if you don't have any other jQuery 
already loaded.

5. Refresh the cache again

Now you can use the module :)

If you find a problem, obsolete or improper code or such, please contact us 
at http://mageinn.com/contactus/


Compatibility With Other Modules
--------------------------------

Should be compatible as long as the following models are not overridden:

Mage_Catalog_Model_Layer_Filter_Item
Mage_Catalog_Model_Layer_Filter_Price
Mage_Catalog_Model_Layer_Filter_Attribute
Mage_Catalog_Model_Layer_Filter_Category
Mage_Catalog_Model_Resource_Layer_Filter_Price
Mage_Catalog_Model_Resource_Layer_Filter_Attribute
Mage_CatalogSearch_Model_Layer_Filter_Attribute
Mage_Catalog_Model_Layer
Mage_CatalogSearch_Model_Layer


F.A.Q.
-----
Q.: How to enable price slider in search results layered navigation?

A.: In the admin panel go to "Catalog -> Attributes -> Manage Attributes". 
Edit the attribute with code 'price' and enable the following setting: 
"Use In Search Results Layered Navigation". 
