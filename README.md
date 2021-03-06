# Cloudflare Dynamic DNS

## INTRODUCTION
This script allows you to update your DNS records at Cloudflare directly from your router, essentially creating and running your own DynDNS service. The update syntax is based on the standard [DynDNS syntax](https://help.dyn.com/remote-access-api/perform-update/).

## REQUIREMENTS
 - Cloudflare account with your own domain (e.g example.com) and an A Record already created (e.g ddns.example.com)
 - A router with DynDNS service that can call a custom url with the following syntax
    `https://<username>:<password>@example.com/nic/update?myip=<ip_address>&hostname=<hostname>`
 - Webserver with PHP (for self-hosting)

## USAGE (Assume your domain is example.com)

1. Copy the folder **/nic/** to the root document of example.com.
2. Login to your router administration interface.
3. Find the **DDNS Configuration** section and fill it as such (this may vary depending on the make and model of your router).

  	- Enable DDNS:      *enabled*
  	- WAN Name:         *keep default*
  	- Service Provider: *dyndns-custom*
  	- Host Name:        *example.com*
  	- Service Port:     *443* (80 if using HTTP)
  	- Domain Name:      *ddns.example.com*
  	- User Name:        *your_cloudflare_email_address*
  	- Password:         *your_cloudflare_global_api_key*

4. Save & apply.

## ADDITIONAL INFO

If you do not have a webserver with PHP, you may skip **step 1.** and change your Host Name in **step 3.** as follows:

  	- Host Name: cloudflare-ddns.olivierchung.com
