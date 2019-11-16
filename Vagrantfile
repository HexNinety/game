Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/bionic64"
  config.vm.network "forwarded_port", guest: 80, host: 8080, auto_correct: true
  config.vm.provision "shell", inline: <<-SCRIPT
    # Install Lighttpd and dependencies.
    apt update
    apt install --yes lighttpd php-fpm

    # Configure PHP-FPM as a Lighttpd module.
    echo '' >> /etc/lighttpd/lighttpd.conf
    cat >> /etc/lighttpd/lighttpd.conf <<END_CONF
    server.tag = ""
    server.modules += ( "mod_fastcgi" )
    fastcgi.server = (
        ".php" => (
            "localhost" => (
                "socket" => "/run/php/php7.2-fpm.sock",
                "broken-scriptfilename" => "enable"
            )
        )
    )
END_CONF
    systemctl restart lighttpd.service

    # Reset install path and its permissions.
    rm -rf /var/www/html
    ln -s /vagrant /var/www/html
  SCRIPT
end
