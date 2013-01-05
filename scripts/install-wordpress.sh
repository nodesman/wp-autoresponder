git clone git://github.com/WordPress/WordPress.git wordpress
cd wordpress
last_tag=$(git tag | tail -1);
git reset --hard $last_tag
cd ..
cp ./scripts/wp-config.php ./wordpress/
git clone --recurse-submodules git://github.com/andreascreten/wp-cli.git ./wp-cli  
cd ./wp-cli  
sudo utils/build-dev
cd ../wordpress
wp core install --blog=12.0.0.1 --email=admin@127.0.0.1 --db-name=wordpress --db-user=root --db-pass="" --db-host=127.0.0.1 --site_url="http://localhost/wordpress/" --site_title="Brown Fox" --admin_email="raj@wpresponder.com" --admin_password="password"
cp -r ../src ./wp-content/plugins/wp-responder-email-autoresponder-and-newsletter-plugin
cp -v ../scripts/wpr-activator.php .
touch .wp-tests-version
