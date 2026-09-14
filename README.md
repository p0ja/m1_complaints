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
