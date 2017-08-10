# ejabberd_gnusocial
ejabberd module to authenticate with GNUSocial servers.

## Installing
1. Download the xmpp_auth.php file and put it in ```/etc/ejabberd``` on your server.
2. Modify the config variables in the php script, and make sure your ejabberd configuration enables http_bind (not https_bind) (See: https://hastebin.com/aveyeritas.rb for a decent configuration with admin ACL, TLS/SSL over port 5222 and s2s enabled with conference rooms.)
3. Modify ```ejabberd.yml``` with:
```
auth_method: [external]
extauth_program: "/usr/bin/php /etc/ejabberd/xmpp_auth.php"
extauth_instances: 3
```
4. Restart the ejabberd server.
5. Now we need to make changes to apache2 to proxy to port 5280 when browsing to /http-bind on the instance. (With nginx, you need to proxy using it's proxy module, I don't use nginx so IDK how to do that.
```
a2enmod proxy proxy_http proxy_ajp 
```
6. then add the following to your sites conf file in apache (this is to help with cross-scripting/xss/cross-site issues in modern browsers):
```
RewriteEngine On
RewriteRule ^/http-bind$ http://localhost:5280/http-bind [P,L]
```
7. Restart apache2
8. Confirm that going to https://example.com/http-bind brings up ejabberd's built in web server.
9. Once that is done, ejabberd should begin to authenticate against your GNU Social instance.
