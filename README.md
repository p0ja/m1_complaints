Magento 1 Complaints module.
Requirements:
- PHP ver. 5.6
- Magento ver. 1.7.0.2
- MDN_AdvancedStock module ver. 1.9.1
- MySQL server ver. 5.5.32+

Installation:
- with modman: `modman clone <repository url>`, or copy the files to the paths listed in the `modman` file
- add the shop's sales extension module (sales/item model, salesext29 events) to `<depends>` in `app/etc/modules/M1_Complaints.xml`
- clear the cache and open the admin panel once, so the setup scripts run up to version 0.1.4

Upgrading from 0.1.3:
- upgrade 0.1.4 renames the Polish columns of `complaints_items` to English names
- admin URLs moved from `complaints/adminhtml_complaint/*` to `adminhtml/complaint/*`; update links in other modules
- complaint documents are stored in `var/complaints/` instead of the public `media/complaints/`; move existing files there
- grant the *Sales > Orders > Complaints* ACL resource to the admin roles that manage complaints
- complaints saved before this version may have a `complaint_status` or `is_return` value shifted by one (see Fixes)

Changes (after-time-fixes):

Fixes:
- `Mage::getHelper()` does not exist in Magento 1; all calls use `Mage::helper()`
- the forms, grid, export and models used English column names while the table kept the Polish ones, so most of the complaint form was not saved; upgrade 0.1.4 renames the columns
- the install script (`na_stanie => 1`) and upgrade 0.1.2 (`AFTER comments`) contained invalid SQL
- the Excel export called the undefined `getExtItem()`, shifted its columns and never filled the return date or purchase cost
- the export filtered the selected complaints by `item_id` instead of `entity_id`
- file operations received a media URL instead of a filesystem path, so uploads and deletes did not work
- the status and return dropdowns saved a value shifted by one (`array_unshift()` renumbered the keys)
- the tab form called block methods on a helper, and tab uploads (`complaint[file1]`) were never found
- errors were shown as success messages, a failed "new complaint" ended on a blank page, and deleting a record said "saved" and left its files on disk
- the deadline ignored the configured delay period, and filtering the grid by purchase cost caused an SQL error
- an observer for `stock_transfer_after_save` pointed to a method that does not exist

Security:
- SQL injection in the export filter; the ids are cast to integers and bound
- the admin controller had no `_isAllowed()` check, so every admin role could use it
- stored XSS through uploaded file names, complaint numbers and order data; values are escaped
- complaint documents were publicly downloadable from `media/`; they are served by `downloadAction()` behind the ACL check
- uploads with the same name overwrote each other; a replaced file is deleted only after the new one is saved

Magento 1 conventions:
- the admin controller hooks into the adminhtml router (SUPEE-6788) instead of its own admin frontName; the unused frontend router was removed
- added the module declaration, the admin layout and order view tab template, the Polish translations and the `modman` mapping, which were missing from the repository
- resource models moved from `Model/Mysql4` to `Model/Resource`, setup scripts renamed from `mysql4-*` to `install-*`/`upgrade-*`
- records are created through the model instead of a raw SQL helper (`Helper_Sql` removed)
- labels are translated through the module helper, and the configuration screen has its own `M1config` tab
- fewer queries in the grid and export: joined order and shipment data is reused instead of loading them for every row
