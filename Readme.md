This module sends order information to track to service Northbeam(https://www.northbeam.io/)

On order place, on order close with creditdemo, on order cancel - corresponding order data should be sent to northbeam

To prevent any delays, need to send order data to external system using Queue - this is implemented here.
We use observers: 
* sales_order_place_after
* order_cancel_after
* sales_order_creditmemo_save_after

And publish order increment id to queue.
Then consumer gets order id, prepares data for send. Then send to northbeam by curl http adapter.

Northbeam credentials can be configured in Northbeam section in Stores->Configuration