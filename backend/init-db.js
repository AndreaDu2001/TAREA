#!/usr/bin/env node

/**
 * Script para inicializar la base de datos MySQL en Render
 * Se ejecuta automáticamente en el buildCommand del backend
 */

const mysql = require('mysql2/promise');

async function initializeDatabase() {
  try {
    console.log('🔄 Inicializando base de datos...');
    
    const connection = await mysql.createConnection({
      host: process.env.MYSQL_HOST || 'localhost',
      user: process.env.MYSQL_USERNAME || 'root',
      password: process.env.MYSQL_PASSWORD || '',
      port: process.env.MYSQL_PORT || 3306,
    });

    console.log('✅ Conectado a MySQL');

    // Crear base de datos si no existe
    await connection.execute('CREATE DATABASE IF NOT EXISTS service_db');
    console.log('✅ Base de datos creada/verificada');

    // Usar la base de datos
    await connection.execute('USE service_db');

    // Crear tabla si no existe
    const createTableSQL = `
      CREATE TABLE IF NOT EXISTS products (
        id INT NOT NULL AUTO_INCREMENT,
        data LONGTEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
      )
    `;
    
    await connection.execute(createTableSQL);
    console.log('✅ Tabla products creada/verificada');

    // Insertar dato de ejemplo si la tabla está vacía
    const [rows] = await connection.execute('SELECT COUNT(*) as count FROM products');
    
    if (rows[0].count === 0) {
      await connection.execute('INSERT INTO products (data) VALUES (?)', ['Computer Table']);
      console.log('✅ Dato de ejemplo insertado');
    }

    await connection.end();
    console.log('✅ Base de datos inicializada correctamente');
    process.exit(0);

  } catch (error) {
    console.error('❌ Error inicializando BD:', error.message);
    // No fallar si hay error - dejar que el servicio intente conectarse
    process.exit(0);
  }
}

// Ejecutar si se llama directamente
if (require.main === module) {
  initializeDatabase();
}

module.exports = initializeDatabase;
