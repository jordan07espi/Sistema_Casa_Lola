-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: db
-- Tiempo de generación: 25-11-2025 a las 05:37:33
-- Versión del servidor: 8.4.7
-- Versión de PHP: 8.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_casalola_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int NOT NULL,
  `cedula` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nombre` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `cedula`, `nombre`, `telefono`, `activo`, `fecha_registro`) VALUES
(1, '2350637217', 'JORDAN ESPINOSA', '0986780565', 0, '2025-11-24 18:30:41'),
(2, '1714309844', 'LUIS ESPINOSA', '0986780565', 1, '2025-11-24 18:30:58'),
(3, '2350637217', 'JORDAN ESPINOSA', '0986780565', 1, '2025-11-24 18:39:33'),
(4, '2300533193', 'HECTOR CEDEÑO', '0985134893', 1, '2025-11-24 21:30:01'),
(5, '1712396199', 'ENMA TIPAN', '0982826252', 1, '2025-11-25 03:47:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_pedido`
--

CREATE TABLE `detalles_pedido` (
  `id_detalle` int NOT NULL,
  `id_pedido` int NOT NULL,
  `id_producto` int NOT NULL,
  `cantidad` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_pedido`
--

INSERT INTO `detalles_pedido` (`id_detalle`, `id_pedido`, `id_producto`, `cantidad`) VALUES
(18, 6, 1, 1),
(19, 6, 4, 2),
(20, 6, 3, 50),
(21, 6, 6, 5),
(22, 6, 9, 3),
(23, 7, 1, 1),
(24, 7, 5, 1),
(25, 8, 1, 1),
(26, 8, 2, 2),
(27, 9, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intentos_login_fallidos`
--

CREATE TABLE `intentos_login_fallidos` (
  `id` int NOT NULL,
  `cedula` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_intento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int NOT NULL,
  `codigo_pedido` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_cliente` int NOT NULL,
  `id_usuario` int NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_entrega` date NOT NULL,
  `hora_entrega` time NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente','Entregado','Cancelado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pendiente',
  `observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `evidencia_foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `codigo_pedido`, `id_cliente`, `id_usuario`, `fecha_creacion`, `fecha_entrega`, `hora_entrega`, `total`, `estado`, `observaciones`, `evidencia_foto`) VALUES
(6, '2025_43', 3, 1, '2025-11-25 02:45:01', '2025-11-30', '07:00:00', 200.00, 'Entregado', 'La pierna viene madura', NULL),
(7, '2025_LL', 2, 1, '2025-11-25 03:28:31', '2025-12-24', '11:00:00', 160.00, 'Pendiente', 'Trae bandeja', NULL),
(8, '2025_67', 5, 1, '2025-11-25 03:51:23', '2025-11-30', '08:00:00', 120.00, 'Entregado', 'La perna viene madura y trae una bandeja', NULL),
(9, '2025_28', 4, 2, '2025-11-25 03:53:42', '2025-11-25', '03:53:00', 129.00, 'Cancelado', '', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos_tillos`
--

CREATE TABLE `pedidos_tillos` (
  `id_tillo` int NOT NULL,
  `id_pedido` int NOT NULL,
  `codigo_tillo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `estado` enum('Pendiente','Entregado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pendiente',
  `id_producto` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos_tillos`
--

INSERT INTO `pedidos_tillos` (`id_tillo`, `id_pedido`, `codigo_tillo`, `estado`, `id_producto`) VALUES
(7, 6, '2025_43', 'Entregado', 1),
(8, 6, '2025_67', 'Entregado', 4),
(9, 6, '2025_129', 'Entregado', 4),
(10, 7, '2025_LL', 'Pendiente', 1),
(11, 7, '2025_87', 'Pendiente', 5),
(12, 8, '2025_67', 'Entregado', 1),
(13, 8, '2025_KK', 'Entregado', 2),
(14, 8, '2025_123', 'Entregado', 2),
(15, 9, '2025_28', 'Pendiente', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int NOT NULL,
  `nombre_producto` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `requiere_tillo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre_producto`, `activo`, `requiere_tillo`) VALUES
(1, 'Chancho', 1, 1),
(2, 'Costilla', 1, 1),
(3, 'Tortillas', 1, 0),
(4, 'Piernas', 1, 1),
(5, 'Pavo', 1, 1),
(6, 'Agrio', 1, 0),
(7, 'Brazos', 1, 1),
(8, 'Pollos', 1, 1),
(9, 'Motes', 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int NOT NULL,
  `nombre_rol` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`) VALUES
(1, 'Administrador'),
(2, 'Empleado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int NOT NULL,
  `nombre_completo` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `cedula` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_rol` int NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre_completo`, `cedula`, `password`, `id_rol`, `activo`) VALUES
(1, 'Jordan Espinosa', '2350637217', '$2y$10$VWjzn/QJiNd/XhUC/E5TSOA2P./VddqiuVlxtD7zOEXbxiZ/iHRL6', 1, 1),
(2, 'ENMA TIPAN', '1712396199', '$2y$10$GeVdlvrtvAL8iMYhZ3Uuh./1MYhqKUbnNBtSwolWf0Ulcc89H39hi', 1, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`);

--
-- Indices de la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `fk_detalles_pedido` (`id_pedido`),
  ADD KEY `fk_detalles_producto` (`id_producto`);

--
-- Indices de la tabla `intentos_login_fallidos`
--
ALTER TABLE `intentos_login_fallidos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `fk_pedidos_cliente` (`id_cliente`),
  ADD KEY `fk_pedidos_usuario` (`id_usuario`);

--
-- Indices de la tabla `pedidos_tillos`
--
ALTER TABLE `pedidos_tillos`
  ADD PRIMARY KEY (`id_tillo`),
  ADD KEY `fk_tillos_pedido` (`id_pedido`),
  ADD KEY `fk_tillos_producto` (`id_producto`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `idx_cedula_unica` (`cedula`),
  ADD KEY `fk_usuarios_roles_idx` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  MODIFY `id_detalle` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `intentos_login_fallidos`
--
ALTER TABLE `intentos_login_fallidos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `pedidos_tillos`
--
ALTER TABLE `pedidos_tillos`
  MODIFY `id_tillo` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  ADD CONSTRAINT `fk_detalles_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detalles_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedidos_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `fk_pedidos_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `pedidos_tillos`
--
ALTER TABLE `pedidos_tillos`
  ADD CONSTRAINT `fk_tillos_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tillos_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
