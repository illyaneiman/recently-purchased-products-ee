# Ineiman_RecentlyPurchased for Magento2 EE
This module has the capability to sort the recently purchased item.

## Overview
Module provide possibility to display recently purchased products in widget.
Widget added through Dynamic Block (install via patch with module) - this helps avoid cache issues.

## Features
- Add Recently Purchased Widget to show recently purchased products at different pages.
- Products in Widget filtered by category and its child categories if widget added to PLP.

# User Manual
## Usage in Admin
###
- Go to Admin Panel -> Stores -> Configuration -> Ineiman -> Recently Purchased
- Enable extension.

### Dynamic Block
- Go to Admin Panel -> Content -> Dynamic Blocks.
- Find "Recently Purchased Widget" and edit, if needed (for example: title, products_count)

### CMS Pages and Blocks
- Go to Amin Panel -> Content -> Pages / Blocks
- Select CMS Page/Block for edit.
- Open "Content" fieldset.
- Choose "Edit with Pagebuilder" and add Dynamic Block "Recently Purchased Widget".
- Save CMS Page/Blocks.
