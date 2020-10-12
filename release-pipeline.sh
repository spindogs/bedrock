# Make sure the necessary directories are in place
mkdir -p ~/httpdocs/releases/$(Build.BuildNumber)
mkdir -p ~/httpdocs/uploads
echo "Options +FollowSymLinks -SymLinksIfOwnerMatch" > ~/httpdocs/.htaccess

# unzip the new build
unzip -o ~/httpdocs/$(Build.BuildId).zip -d httpdocs/releases/$(Build.BuildNumber)

# remove the uncessary files
rm -f ~/httpdocs/$(Build.BuildId).zip
rm -rf ~/httpdocs/releases/$(Build.BuildNumber)/web/app/uploads

# link the necessary files to their place within the build
ln -nfs ~/httpdocs/uploads ~/httpdocs/releases/$(Build.BuildNumber)/web/app/uploads
ln -nfs ~/httpdocs/.env ~/httpdocs/releases/$(Build.BuildNumber)/.env

# link the build to `current`
ln -nfs ~/httpdocs/releases/$(Build.BuildNumber) ~/httpdocs/current

# configure the server to serve from /var/www/vhost/SITENAME/httpdocs/current/web/