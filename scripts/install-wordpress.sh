git clone git://github.com/WordPress/WordPress.git wordpress
cd wordpress
last_tag=$(git tag | tail -1);
git reset --hard $last_tag

