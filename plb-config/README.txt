First, edit blocks.php to insert prefixes on a per /24 basis.

Then run daemon-proxy.php to get the JSON representation of the listeners config for those blocks. You may use Insomnia to update the daemon config.

Then, open and edit haproxy.php to setup start port/tiers, and then run it to generate the haproxy config. Set this into the right place.

When all's said and done, you can use check.php (run without any arguments to see how to use it) to validate that your setup proxies are working as expected.