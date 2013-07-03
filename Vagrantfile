Vagrant.configure("2") do |config|
  config.vm.define :web do |config|
    config.vm.box = "precise32"
    config.vm.box_url = "http://files.vagrantup.com/precise32.box"
    config.vm.network :private_network, ip: "192.168.111.200"
    config.vm.provision :ansible do |ansible|
      ansible.playbook = "vagrant/provisioning/playbook.yml"
      ansible.inventory_file = "vagrant/provisioning/ansible_hosts"
    end
  end

end