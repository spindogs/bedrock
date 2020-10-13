# Make the required directories
mkdir -p ~/httpdocs/releases/$(Build.BuildNumber)
mkdir -p ~/httpdocs/uploads
mkdir -p ~/httpdocs/plugins
# Ensure the server follows Symlinks
echo "Options +FollowSymLinks -SymLinksIfOwnerMatch" > ~/httpdocs/.htaccess
# Unzip the build
unzip -o ~/httpdocs/$(Build.BuildId).zip -d httpdocs/releases/$(Build.BuildNumber)

# Copy any uploads and plugins from the repo into the outside directories
rsync -au ~/httpdocs/releases/$(Build.BuildNumber)/web/app/plugins/ ~/httpdocs/plugins/
rsync -au ~/httpdocs/releases/$(Build.BuildNumber)/web/app/uploads/ ~/httpdocs/uploads/

# Remove the build artifact and unnecssary folders
rm -f ~/httpdocs/$(Build.BuildId).zip
rm -rf ~/httpdocs/releases/$(Build.BuildNumber)/web/app/uploads
rm -rf ~/httpdocs/releases/$(Build.BuildNumber)/web/app/plugins

# Link the outside files to the deployed build
ln -nfs ~/httpdocs/uploads ~/httpdocs/releases/$(Build.BuildNumber)/web/app/uploads
ln -nfs ~/httpdocs/plugins ~/httpdocs/releases/$(Build.BuildNumber)/web/app/plugins
ln -nfs ~/httpdocs/.env ~/httpdocs/releases/$(Build.BuildNumber)/.env
ln -nfs ~/httpdocs/releases/$(Build.BuildNumber) ~/httpdocs/current

# Configure the websever to serve from `/httpdocs/current/web/`