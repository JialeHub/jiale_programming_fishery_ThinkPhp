/*
 Navicat Premium Data Transfer

 Source Server         : 127.0.0.1
 Source Server Type    : MySQL
 Source Server Version : 80021
 Source Host           : localhost:3306
 Source Schema         : jiale_programming_fishery

 Target Server Type    : MySQL
 Target Server Version : 80021
 File Encoding         : 65001

 Date: 12/06/2021 15:53:54
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for article
-- ----------------------------
DROP TABLE IF EXISTS `article`;
CREATE TABLE `article`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '标题',
  `keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '关键词',
  `description` varchar(3000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '描述',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '内容',
  `cover` varchar(5000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '封面',
  `type` int NULL DEFAULT NULL COMMENT '类型 0服务与支持 1案例展示 2公司动态 3合作伙伴',
  `remark` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  `status` int NULL DEFAULT 1 COMMENT '状态(0隐藏 1启用)',
  `create_time` datetime NULL DEFAULT NULL,
  `create_id` int NULL DEFAULT NULL COMMENT '创建者ID',
  `update_time` datetime NULL DEFAULT NULL,
  `update_id` int NULL DEFAULT NULL,
  `delete_time` datetime NULL DEFAULT NULL,
  `delete_id` int NULL DEFAULT NULL,
  `link` varchar(3000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '外链',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 85 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of article
-- ----------------------------

-- ----------------------------
-- Table structure for article_file
-- ----------------------------
DROP TABLE IF EXISTS `article_file`;
CREATE TABLE `article_file`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_id` int NULL DEFAULT NULL,
  `file_id` int NULL DEFAULT NULL,
  `create_time` datetime NULL DEFAULT NULL,
  `creat_id` int NULL DEFAULT NULL,
  `delete_time` datetime NULL DEFAULT NULL,
  `delete_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of article_file
-- ----------------------------

-- ----------------------------
-- Table structure for dept
-- ----------------------------
DROP TABLE IF EXISTS `dept`;
CREATE TABLE `dept`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` int NULL DEFAULT NULL COMMENT '上级',
  `name` varchar(10000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '名称',
  `status` int NULL DEFAULT NULL COMMENT '状态 0禁用 1启用',
  `sort` int NULL DEFAULT NULL COMMENT '排序',
  `create_time` datetime NULL DEFAULT NULL,
  `create_id` int NULL DEFAULT NULL COMMENT '创建者ID',
  `update_time` datetime NULL DEFAULT NULL,
  `update_id` int NULL DEFAULT NULL COMMENT '编辑者ID',
  `delete_time` datetime NULL DEFAULT NULL COMMENT '软删除时间',
  `delete_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 789 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of dept
-- ----------------------------
INSERT INTO `dept` VALUES (1, NULL, 'Root', 1, 0, '2021-03-21 21:54:26', 1, '2021-03-28 01:37:15', 1, NULL, NULL);
INSERT INTO `dept` VALUES (2, 1, 'Admin', 1, 1, '2021-03-21 21:54:31', 2, '2021-03-28 01:39:06', 1, NULL, NULL);

-- ----------------------------
-- Table structure for file
-- ----------------------------
DROP TABLE IF EXISTS `file`;
CREATE TABLE `file`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `original_name` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '原文件名',
  `mime_type` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件类型',
  `name` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件名',
  `suffix` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件名后缀',
  `size` int NULL DEFAULT NULL COMMENT '文件大小',
  `pid` int NULL DEFAULT NULL COMMENT '父id',
  `remark` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注信息',
  `info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '其他文件信息，JSON格式',
  `path` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '绝对服务器文件路径',
  `src` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '相对引用地址',
  `tag` int NULL DEFAULT NULL COMMENT '标记(可用于分类)',
  `type` int NULL DEFAULT NULL COMMENT '类型 0目录 1文件',
  `create_time` datetime NULL DEFAULT NULL,
  `create_id` int NULL DEFAULT NULL,
  `update_time` datetime NULL DEFAULT NULL,
  `update_id` int NULL DEFAULT NULL,
  `delete_time` datetime NULL DEFAULT NULL,
  `delete_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 516 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of file
-- ----------------------------

-- ----------------------------
-- Table structure for log
-- ----------------------------
DROP TABLE IF EXISTS `log`;
CREATE TABLE `log`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_info` varchar(5000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'IP信息(JSON)',
  `ip_addr` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'IP物理地址',
  `x_forwarded_for` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '多重转发的地址(真实地址)',
  `request_user_agent` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '当前请求的设备信息',
  `request_referer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '客户端当前访问前端页面URL',
  `domain` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '包含协议的请求域名',
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '当前请求完整URL',
  `method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '请求方法',
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '当前请求的资源类型',
  `status` float(10, 0) NULL DEFAULT NULL COMMENT '状态码',
  `remote_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '请求/代理IP',
  `protocol` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '当前请求的SERVER_PROTOCOL',
  `request_accept` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '当前请求的HTTP_ACCEPT',
  `request_accept_encoding` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '编码方式',
  `request_accept_language` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '当前请求的语言',
  `request_connection` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '连接',
  `request_content_length` double NULL DEFAULT NULL COMMENT '长度',
  `request_content_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '当前请求的CONTENT_TYPE',
  `time` datetime NULL DEFAULT NULL COMMENT '发起请求的时间',
  `duration` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '处理时长',
  `create_time` datetime NULL DEFAULT NULL,
  `update_time` datetime NULL DEFAULT NULL,
  `delete_time` datetime NULL DEFAULT NULL,
  `session_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '对话身份标识(设备)',
  `authorization` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'token',
  `user_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '用户ID',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 221832 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of log
-- ----------------------------

-- ----------------------------
-- Table structure for menu
-- ----------------------------
DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` int NULL DEFAULT NULL COMMENT '父id',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '菜单标题',
  `type` int NULL DEFAULT NULL COMMENT '类型 0目录 1菜单 2按钮/权限',
  `remark` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '图标',
  `sort` int NULL DEFAULT NULL COMMENT '排序',
  `status` int NULL DEFAULT NULL COMMENT '状态',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '组件名称',
  `component` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '组件路径(\'@/views\' + [\'component\'])',
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '路由地址',
  `auth` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '权限标识',
  `model` int NULL DEFAULT NULL COMMENT '模式 0常规 1外链弹窗 2外链内嵌',
  `cache` int NULL DEFAULT NULL COMMENT '缓存',
  `add_routes` int NULL DEFAULT NULL COMMENT '前端动态路由',
  `hidden` int NULL DEFAULT NULL COMMENT '隐藏',
  `redirect` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '重定向地址，在面包屑中点击会重定向去的地址',
  `always_show` int NULL DEFAULT NULL COMMENT '一直显示根路由 0否 1是',
  `breadcrumb` int NULL DEFAULT NULL COMMENT '在breadcrumb面包屑中显示',
  `affix` int NULL DEFAULT NULL COMMENT '固定在tags-view中',
  `active_menu` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '侧边栏高亮的路由',
  `create_time` datetime NULL DEFAULT NULL,
  `create_id` int NULL DEFAULT NULL,
  `update_time` datetime NULL DEFAULT NULL,
  `update_id` int NULL DEFAULT NULL,
  `delete_time` datetime NULL DEFAULT NULL,
  `delete_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 138 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of menu
-- ----------------------------
INSERT INTO `menu` VALUES (1, NULL, 'Jiale - ADMIN', 0, '根布局(供一级菜单挂载)', NULL, 0, 1, 'Layout', 'Layout', '/', NULL, NULL, NULL, 1, 0, 'home', NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, '2021-06-11 08:43:24', 1, NULL, NULL);
INSERT INTO `menu` VALUES (2, 100, '登录', 2, 'all ', NULL, 100, 1, NULL, NULL, NULL, '/user/login', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (3, 100, '注销', 2, 'all ', NULL, 100, 1, NULL, NULL, NULL, '/user/logout', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (4, 100, '获取用户信息', 2, 'all ', NULL, 100, 1, NULL, NULL, NULL, '/user/info', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, '2021-04-01 08:25:51', 1, NULL, NULL);
INSERT INTO `menu` VALUES (5, 100, '获取菜单更新权限', 2, 'all 1', NULL, 100, 1, NULL, NULL, NULL, '/user/initMenu', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, '2021-04-01 08:21:55', 1, NULL, NULL);
INSERT INTO `menu` VALUES (6, 100, '注册', 2, 'all ', NULL, 100, 1, NULL, NULL, NULL, '/user/register', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (7, 100, '修改个人信息', 2, 'all ', NULL, 100, 1, NULL, NULL, NULL, '/user/updateInfo', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (8, 100, '获取登录验证码', 2, 'all ', NULL, 100, 1, NULL, NULL, NULL, '/user/getLoginCaptcha', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (9, 100, 'sessionToken验证并设置新密码', 2, 'all ', NULL, 100, 1, NULL, NULL, NULL, '/user/updatePassBySessionToken', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (10, 100, '邮件验证并返回重置密码临时Token', 2, 'all ', NULL, 100, 1, NULL, NULL, NULL, '/user/getForgetPassToken', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (11, 100, '发送找回密码验证码邮件', 2, 'all ', NULL, 100, 1, NULL, NULL, NULL, '/user/sendForgetEmail', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (12, 100, '用户获取(列表)', 2, 'admin', NULL, 100, 1, NULL, NULL, NULL, '/user/list', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (13, 100, '用户获取(指定)', 2, 'admin', NULL, 100, 1, NULL, NULL, NULL, '/user/read', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (14, 100, '用户添加', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/user/add', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (15, 100, '用户删除', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/user/del', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (16, 100, '用户修改', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/user/edit', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (17, 100, '用户导出', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/user/export', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (18, 100, 'Wiki文档', 2, 'all', NULL, 100, 1, NULL, NULL, NULL, '/swagger/explore', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (19, 100, '操作日志', 2, 'admin', NULL, 100, 1, NULL, NULL, NULL, '/log', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (20, 100, '上传文件', 2, 'all ', NULL, 100, 1, NULL, NULL, NULL, '/file', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (21, 100, '查看文件信息', 2, 'admin', NULL, 100, 1, NULL, NULL, NULL, '/file/:id', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (22, 100, '上传Base64图片', 2, 'all ', NULL, 100, 1, NULL, NULL, NULL, '/file/base64', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (23, 100, '文章获取(列表)', 2, 'all ', NULL, 100, 1, NULL, NULL, NULL, '/article/list', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (24, 100, '文章获取(指定)', 2, 'all ', NULL, 100, 1, NULL, NULL, NULL, '/article/read', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (25, 100, '文章添加', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/article/add', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (26, 100, '文章删除', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/article/del', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (27, 100, '文章修改', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/article/edit', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (28, 100, '文章导出', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/article/export', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (30, 1, '首页', 1, 'admin', 'mdi-home', 1, 1, 'Home', 'home/index', 'home', '', 0, 1, 1, 0, NULL, 1, 1, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (32, 1, '系统管理', 0, 'admin', 'mdi-cog', 2, 1, 'System', 'Router', 'system', NULL, NULL, 0, 1, 0, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, '2021-04-13 21:29:14', 1, NULL, NULL);
INSERT INTO `menu` VALUES (33, 32, '用户管理', 1, 'admin', 'mdi-account', 3, 1, 'User', 'system/user/index', 'user', NULL, 0, 0, 1, 0, NULL, 1, 1, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (34, 32, '部门管理', 1, 'admin', 'mdi-group', 4, 1, 'Dept', 'system/dept/index', 'dept', NULL, 0, 0, 1, 0, NULL, 1, 1, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (35, 32, '菜单管理', 1, 'admin', 'mdi-menu', 5, 1, 'Menu', 'system/menu/index', 'menu', NULL, 0, 0, 1, 0, NULL, 1, 1, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (36, 32, '角色管理', 1, 'admin', 'mdi-account-group', 6, 1, 'Role', 'system/role/index', 'role', NULL, 0, 0, 1, 0, NULL, 1, 1, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (40, 100, '部门获取(列表)', 2, 'admin', NULL, 100, 1, NULL, NULL, NULL, '/dept/list', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (41, 100, '部门获取(指定)', 2, 'admin', NULL, 100, 1, NULL, NULL, NULL, '/dept/read', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (42, 100, '部门添加', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/dept/add', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (43, 100, '部门删除', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/dept/del', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (44, 100, '部门修改', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/dept/edit', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (45, 100, '部门导出', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/dept/export', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (50, 100, '菜单获取(列表)', 2, 'admin', NULL, 100, 1, NULL, NULL, NULL, '/menu/list', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (51, 100, '菜单获取(指定)', 2, 'admin', NULL, 100, 1, NULL, NULL, NULL, '/menu/read', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (52, 100, '菜单添加', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/menu/add', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (53, 100, '菜单删除', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/menu/del', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (54, 100, '菜单修改', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/menu/edit', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (55, 100, '菜单导出', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/menu/export', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (60, 100, '角色获取(列表)', 2, 'admin', NULL, 100, 1, NULL, NULL, NULL, '/role/list', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (61, 100, '角色获取(指定)', 2, 'admin', NULL, 100, 1, NULL, NULL, NULL, '/role/read', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (62, 100, '角色添加', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/role/add', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (63, 100, '角色删除', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/role/del', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (64, 100, '角色修改', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/role/edit', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (65, 100, '角色导出', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/role/export', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (70, 1, '开发工具', 0, 'admin', 'mdi-tools', 7, 1, 'Experiment', 'Router', 'experiment', NULL, NULL, 0, 1, 0, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, '2021-04-13 21:55:19', 1, NULL, NULL);
INSERT INTO `menu` VALUES (71, 70, 'mdi图标库', 1, 'admin', 'mdi-emoticon', 9, 1, 'MdiIcon', 'experiment/mdiIcon/index', 'mdiIcon', NULL, 0, 0, 1, 0, NULL, 1, 1, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (100, NULL, '后台权限', 0, 'all', NULL, 100, 1, NULL, NULL, NULL, '/', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, '2021-04-01 08:46:52', 1, NULL, NULL);
INSERT INTO `menu` VALUES (110, 70, 'Swagger', 1, 'swagger文档', 'mdi-file-document-outline', 8, 1, 'swagger', 'experiment/swagger/index', 'swagger', NULL, 0, 0, 1, 0, NULL, 1, 1, 1, NULL, '2021-06-12 15:46:26', 1, '2021-04-13 21:40:47', 1, NULL, NULL);
INSERT INTO `menu` VALUES (111, 1, '文章', 1, '文章', 'mdi-briefcase-check-outline', 14, 1, 'article', 'article/index', 'article', NULL, 0, 0, 1, 1, NULL, 1, 1, 1, NULL, '2021-06-12 15:46:26', 1, '2021-06-12 15:35:49', 1, NULL, NULL);
INSERT INTO `menu` VALUES (121, 70, '插件体验', 1, '插件体验', 'mdi-buffer', 10, 1, 'pluginDemo', 'experiment/pluginDemo/index', 'pluginDemo', NULL, 0, 0, 1, 0, NULL, 1, 1, 1, NULL, '2021-06-12 15:46:26', 1, '2021-04-15 09:36:47', 1, NULL, NULL);
INSERT INTO `menu` VALUES (122, 1, '平台管理', 0, '渔业信息采集', 'mdi-briefcase-check', 12, 1, 'Platform', 'Router', 'platform', NULL, NULL, 0, 1, 0, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, '2021-06-10 22:21:24', 1, NULL, NULL);
INSERT INTO `menu` VALUES (123, 122, '照片记录', 1, '照片记录', 'mdi-image', 13, 1, 'Publish', 'platform/publish/index', 'publish', NULL, 0, 0, 1, 0, NULL, 1, 1, 1, NULL, '2021-06-12 15:46:26', 1, '2021-06-10 22:16:56', NULL, NULL, NULL);
INSERT INTO `menu` VALUES (125, 122, '评分汇总', 1, '评分汇总', 'mdi-google-analytics', 14, 1, 'Collect', 'platform/collect/index', 'collect', NULL, 0, 0, 1, 0, NULL, 1, 1, 1, NULL, '2021-06-12 15:46:26', 1, '2021-06-11 09:13:19', NULL, NULL, NULL);
INSERT INTO `menu` VALUES (130, 100, '发布获取(列表)', 2, 'all ', NULL, 100, 1, NULL, NULL, NULL, '/publish/list', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (131, 100, '发布获取(指定)', 2, 'all ', NULL, 100, 1, NULL, NULL, NULL, '/publish/read', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (132, 100, '发布添加', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/publish/add', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (133, 100, '发布删除', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/publish/del', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (134, 100, '发布修改', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/publish/edit', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (135, 100, '发布导出', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/publish/export', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (136, 100, '发布图片下载', 2, 'admin ', NULL, 100, 1, NULL, NULL, NULL, '/publish/downloadFiles', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, NULL, NULL, NULL, NULL);
INSERT INTO `menu` VALUES (137, 100, '获取自己的分数', 2, 'all', NULL, 100, 1, NULL, NULL, NULL, '/publish/collectMe', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, '2021-06-12 09:00:15', 1, NULL, NULL);
INSERT INTO `menu` VALUES (138, 100, '获取用户评分汇总', 2, 'admin', NULL, 100, 1, NULL, NULL, NULL, '/publish/collectList', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, '2021-06-12 11:46:38', NULL, NULL, NULL);
INSERT INTO `menu` VALUES (139, 100, '导出用户评分汇总', 2, 'admin', NULL, 100, 1, NULL, NULL, NULL, '/publish/collectExport', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, '2021-06-12 12:49:45', 1, NULL, NULL);

-- ----------------------------
-- Table structure for publish
-- ----------------------------
DROP TABLE IF EXISTS `publish`;
CREATE TABLE `publish`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '发布标题',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '发布文字内容',
  `score` int NOT NULL DEFAULT -1 COMMENT '评分',
  `evaluate` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '评价',
  `ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '发布IP',
  `ip_info` varchar(3000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'IP查询结果(JSON)',
  `ip_addr` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'IP物理地址',
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '发表单条时的经纬度',
  `location_res` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '位置查询结果',
  `location_res_addr` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '位置查询结果拼接',
  `work_days` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '作业天数',
  `operating_type` int NULL DEFAULT NULL COMMENT '作业类型',
  `fishing_boats` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '渔船号',
  `machine_power` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '主机功率',
  `type` int NULL DEFAULT NULL COMMENT '发表类型 0本船 1其他',
  `status` int NULL DEFAULT NULL COMMENT '状态 0禁用 1公开 2仅自己和管理员可见',
  `create_time` datetime NULL DEFAULT NULL,
  `create_id` int NULL DEFAULT NULL COMMENT '发布者ID',
  `update_time` datetime NULL DEFAULT NULL,
  `update_id` int NULL DEFAULT NULL,
  `delete_time` datetime NULL DEFAULT NULL,
  `delete_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 176 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of publish
-- ----------------------------

-- ----------------------------
-- Table structure for publish_file
-- ----------------------------
DROP TABLE IF EXISTS `publish_file`;
CREATE TABLE `publish_file`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `publish_id` int NULL DEFAULT NULL,
  `file_id` int NULL DEFAULT NULL,
  `create_time` datetime NULL DEFAULT NULL,
  `creat_id` int NULL DEFAULT NULL,
  `delete_time` datetime NULL DEFAULT NULL,
  `delete_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 223 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of publish_file
-- ----------------------------

-- ----------------------------
-- Table structure for role
-- ----------------------------
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '名称',
  `data_scope_model` int NULL DEFAULT NULL COMMENT '数据范围 0全部(deptId=0), 1本级(递归遍历) , 2自定义(role_dept表)',
  `level` int NULL DEFAULT NULL COMMENT '等级',
  `status` int NULL DEFAULT NULL COMMENT '状态',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `create_id` int NULL DEFAULT NULL COMMENT '创建ID',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `update_id` int NULL DEFAULT NULL COMMENT '更新ID',
  `delete_time` datetime NULL DEFAULT NULL COMMENT '软删除时间',
  `delete_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 32 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of role
-- ----------------------------
INSERT INTO `role` VALUES (1, 'Public', 0, 0, 1, '普通访客，禁止删除', '2021-01-01 00:00:00', 1, '2021-06-12 14:59:29', 1, NULL, NULL);
INSERT INTO `role` VALUES (2, 'Root', 0, 0, 1, '超级管理员', '2021-01-01 00:00:00', 1, '2021-04-06 21:28:15', 1, NULL, NULL);
INSERT INTO `role` VALUES (3, 'Admin', 0, 0, 1, '管理员', '2021-01-01 00:00:00', 1, '2021-04-06 20:55:50', 1, NULL, NULL);
INSERT INTO `role` VALUES (4, 'User', 0, 0, 1, '普通用户', '2021-01-01 00:00:00', 1, '2021-04-06 21:28:08', 1, NULL, NULL);

-- ----------------------------
-- Table structure for role_dept
-- ----------------------------
DROP TABLE IF EXISTS `role_dept`;
CREATE TABLE `role_dept`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `dept_id` int NOT NULL,
  `create_time` datetime NULL DEFAULT NULL,
  `create_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of role_dept
-- ----------------------------
INSERT INTO `role_dept` VALUES (1, 2, 1, '2021-06-12 15:46:26', 1);
INSERT INTO `role_dept` VALUES (2, 3, 1, '2021-06-12 15:46:26', 1);
INSERT INTO `role_dept` VALUES (3, 4, 1, '2021-06-12 15:46:26', 1);

-- ----------------------------
-- Table structure for role_menu
-- ----------------------------
DROP TABLE IF EXISTS `role_menu`;
CREATE TABLE `role_menu`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `menu_id` int NOT NULL,
  `create_time` datetime NULL DEFAULT NULL,
  `create_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 647 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of role_menu
-- ----------------------------
INSERT INTO `role_menu` VALUES (16, 2, 19, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (19, 2, 21, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (24, 2, 25, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (26, 2, 26, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (28, 2, 27, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (31, 2, 30, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (33, 2, 32, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (34, 2, 33, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (35, 2, 34, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (36, 2, 35, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (37, 2, 40, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (38, 2, 41, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (39, 2, 42, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (40, 2, 43, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (41, 2, 44, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (42, 2, 70, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (43, 2, 71, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (44, 2, 1, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (46, 2, 45, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (47, 2, 50, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (48, 2, 51, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (49, 2, 52, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (50, 2, 53, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (51, 2, 54, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (52, 2, 55, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (54, 2, 12, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (55, 2, 13, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (56, 2, 14, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (57, 2, 15, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (58, 2, 16, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (59, 2, 17, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (60, 2, 36, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (61, 2, 60, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (62, 2, 61, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (63, 2, 62, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (64, 2, 63, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (65, 2, 64, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (66, 2, 65, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (441, 3, 30, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (448, 3, 1, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (457, 2, 110, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (458, 2, 100, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (459, 2, 111, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (469, 2, 121, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (471, 2, 22, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (472, 2, 18, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (473, 2, 20, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (474, 2, 3, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (475, 2, 23, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (476, 2, 2, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (477, 2, 6, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (478, 2, 7, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (479, 2, 4, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (480, 2, 8, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (481, 2, 5, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (482, 2, 24, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (483, 2, 9, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (484, 2, 10, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (485, 2, 11, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (487, 2, 123, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (488, 2, 122, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (489, 2, 125, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (491, 2, 130, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (492, 2, 131, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (493, 2, 132, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (494, 2, 133, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (495, 2, 134, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (496, 2, 28, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (497, 2, 135, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (541, 4, 23, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (544, 4, 20, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (548, 4, 24, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (559, 4, 22, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (560, 4, 10, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (561, 4, 9, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (570, 4, 2, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (571, 4, 3, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (572, 4, 130, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (574, 4, 5, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (575, 4, 6, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (576, 4, 7, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (577, 4, 8, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (579, 4, 11, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (581, 4, 131, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (582, 4, 132, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (583, 4, 133, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (586, 4, 100, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (587, 3, 12, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (588, 3, 13, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (589, 3, 14, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (590, 3, 15, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (591, 3, 16, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (592, 3, 17, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (593, 3, 23, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (594, 3, 18, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (595, 3, 19, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (596, 3, 20, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (597, 3, 21, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (598, 3, 42, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (599, 3, 51, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (600, 3, 24, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (601, 3, 25, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (602, 3, 26, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (603, 3, 27, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (604, 3, 28, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (605, 3, 41, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (606, 3, 52, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (607, 3, 43, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (608, 3, 44, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (609, 3, 45, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (610, 3, 50, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (611, 3, 22, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (612, 3, 10, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (613, 3, 9, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (614, 3, 55, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (615, 3, 60, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (616, 3, 61, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (617, 3, 62, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (618, 3, 63, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (619, 3, 64, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (620, 3, 4, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (621, 3, 65, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (622, 3, 2, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (623, 3, 3, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (624, 3, 130, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (625, 3, 54, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (626, 3, 5, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (627, 3, 6, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (628, 3, 7, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (629, 3, 8, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (630, 3, 40, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (631, 3, 11, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (632, 3, 53, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (633, 3, 131, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (634, 3, 132, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (635, 3, 133, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (636, 3, 134, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (637, 3, 135, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (638, 3, 100, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (639, 2, 136, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (641, 4, 137, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (642, 3, 136, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (643, 3, 137, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (644, 2, 137, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (647, 3, 138, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (648, 3, 139, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (649, 2, 138, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (650, 2, 139, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (651, 1, 10, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (652, 1, 11, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (653, 1, 9, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (654, 1, 8, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (655, 1, 6, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (656, 1, 2, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (657, 1, 3, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (658, 1, 24, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (659, 1, 23, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (660, 1, 131, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (661, 1, 130, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (662, 1, 100, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (663, 3, 123, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (664, 3, 125, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (665, 3, 111, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (666, 3, 122, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (667, 4, 4, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (668, 1, 4, '2021-06-12 15:46:26', 1);
INSERT INTO `role_menu` VALUES (669, 1, 5, '2021-06-12 15:46:26', 1);

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户唯一标识',
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '登录凭证/用户名',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '密码',
  `realname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '真实姓名',
  `nickname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '昵称',
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '手机',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '邮箱',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '链接',
  `status` int NOT NULL DEFAULT 0 COMMENT '状态 0未激活 1正常 2禁用 3异常',
  `gender` int NULL DEFAULT NULL COMMENT '性别 0保密 1男 2女',
  `birthday` datetime NULL DEFAULT NULL COMMENT '生日',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '头像',
  `area_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '地区编号',
  `profile` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '简介',
  `create_time` datetime NULL DEFAULT NULL,
  `create_id` int NULL DEFAULT NULL,
  `update_time` datetime NULL DEFAULT NULL,
  `update_id` int NULL DEFAULT NULL,
  `delete_time` datetime NULL DEFAULT NULL,
  `delete_id` int NULL DEFAULT NULL,
  `fishing_boats` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '渔船船号',
  `operating_type` int NULL DEFAULT NULL COMMENT '作业方式',
  `machine_power` float NULL DEFAULT NULL COMMENT '主机功率',
  `emergency_call` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '岸上紧急联系人及电话',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 25 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES (1, 'root', '9f2984a3c6aa8b288f4d8d0bd128bdd7564f1fae52f268e6196184954119ddee263e7596184b8769e5da7f4c04e5fa7c9649f42a5c04fa64e360741afbbdd30a', NULL, 'Root', '18675474871', '799670335@qq.com', '', 1, NULL, NULL, '', NULL, NULL, '2021-06-12 15:46:26', 1, '2021-01-01 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `user` VALUES (2, 'admin', 'df7dc827f61422b5af34e414af689c13d02cc0a1b0c989c14802f4ecdf31070a1757845eabc93899c14eec88b6b1313d73a889a9dacb71e151c139cd579f3ab4', NULL, '管理员', NULL, '', '', 1, NULL, NULL, NULL, NULL, NULL, '2021-06-12 15:46:26', 1, '2021-01-01 00:00:00', 1, NULL, NULL, NULL, NULL, NULL, NULL);

-- ----------------------------
-- Table structure for user_dept
-- ----------------------------
DROP TABLE IF EXISTS `user_dept`;
CREATE TABLE `user_dept`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `dept_id` int NOT NULL,
  `create_time` datetime NULL DEFAULT NULL,
  `create_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of user_dept
-- ----------------------------
INSERT INTO `user_dept` VALUES (1, 1, 1, '2021-06-12 15:46:26', 1);
INSERT INTO `user_dept` VALUES (2, 2, 1, '2021-06-12 15:46:26', 1);
INSERT INTO `user_dept` VALUES (3, 1, 2, '2021-06-12 15:46:26', 1);

-- ----------------------------
-- Table structure for user_role
-- ----------------------------
DROP TABLE IF EXISTS `user_role`;
CREATE TABLE `user_role`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  `create_time` datetime NULL DEFAULT NULL,
  `create_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of user_role
-- ----------------------------
INSERT INTO `user_role` VALUES (1, 1, 2, '2021-06-12 15:46:26', 1);
INSERT INTO `user_role` VALUES (2, 2, 3, '2021-06-12 15:46:26', 1);

-- ----------------------------
-- Table structure for user_token
-- ----------------------------
DROP TABLE IF EXISTS `user_token`;
CREATE TABLE `user_token`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `token` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL COMMENT '令牌',
  `expires` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '过期时长',
  `remember_me` tinyint NULL DEFAULT NULL COMMENT '记住我',
  `x_forwarded_for` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '代理IP',
  `remote_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '直接IP',
  `request_user_agent` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '设备信息',
  `session_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '设备对话标识',
  `update_time` datetime NULL DEFAULT NULL,
  `create_time` datetime NULL DEFAULT NULL,
  `delete_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 926 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of user_token
-- ----------------------------

-- ----------------------------
-- Table structure for user_token_auth
-- ----------------------------
DROP TABLE IF EXISTS `user_token_auth`;
CREATE TABLE `user_token_auth`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_token_id` int NOT NULL,
  `auth` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `data_scope` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `data_scope_model` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '0全部 1本级 2自定义',
  `create_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 35146 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of user_token_auth
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
