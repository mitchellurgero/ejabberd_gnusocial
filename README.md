# ejabberd_gnusocial
ejabberd module to authenticate with GNUSocial servers.

## Installing
1. Download the xmpp_auth.php file and put it in ```/etc/ejabberd``` on your server.
2. Modify line ```34``` of that file to match your server.
3. Modify ```ejabberd.yml``` with:
```
auth_method: [external]
extauth_program: "/usr/bin/php /etc/ejabberd/xmpp_auth.php"
extauth_instances: 3
```
4. Restart the ejabberd server.
5. Profit
