<template>
    <div class="sidebar-menu">
      <ul class="menu-list">
        <li v-for="(item, index) in menuItems" :key="index">
          <a :href="item.url" :class="{ 'has-submenu': item.submenu }" @click.prevent="toggleSubmenu(index)">
            {{ item.label }}
            <span v-if="item.submenu" class="submenu-toggle">
              <i v-if="!item.isOpen" class="fas fa-chevron-right"></i>
              <i v-else class="fas fa-chevron-down"></i>
            </span>
          </a>
          <ul v-if="item.submenu && item.isOpen" class="submenu">
            <li v-for="(subItem, subIndex) in item.submenu" :key="subIndex">
              <a :href="subItem.url">{{ subItem.label }}</a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </template>
  
  <script>
  export default {
    name: 'SidebarMenu',
    props: {
      menuItems: {
        type: Array,
        required: true
      }
    },
    methods: {
      toggleSubmenu(index) {
        const item = this.menuItems[index];
        item.isOpen = !item.isOpen;
      }
    }
  };
  </script>
  
  <style scoped>
  .sidebar-menu {
    padding: 20px;
  }
  
  .menu-list {
    list-style: none;
    padding: 0;
    margin: 0;
  }
  
  .menu-list li {
    margin-bottom: 10px;
  }
  
  .has-submenu {
    position: relative;
  }
  
  .submenu-toggle {
    position: absolute;
    top: 50%;
    right: 0;
    transform: translateY(-50%);
  }
  
  .submenu-toggle i {
    margin-left: 5px;
  }
  
  .submenu {
    list-style: none;
    padding: 0;
    margin: 10px 0 0 0;
  }
  
  .submenu li {
    margin-bottom: 5px;
  }
  </style>
  