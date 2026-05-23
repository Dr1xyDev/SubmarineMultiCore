# 📚 **DOCUMENTACIÓN: Cómo Agregar Más Versiones a SubmarineMultiCore**

## 🎯 **Introducción**

**SubmarineMultiCore** actualmente soporta versiones **1.1.0 → 1.21.120** (protocolos 110 → 859). Este sistema multiversión permite que jugadores con diferentes versiones de Minecraft Bedrock se conecten al mismo servidor.

---

## 🔍 **Cómo Funciona el Sistema Multiversión**

El sistema utiliza **números de protocolo** de Minecraft Bedrock. Cada versión del juego tiene un **protocol ID único**:

- **Ejemplo:** Minecraft 1.21.120 = Protocol 859
- **Ejemplo:** Minecraft 1.21.110 = Protocol 844

El archivo principal que controla esto es:
```
/src/pocketmine/network/mcpe/protocol/ProtocolInfo.php
```

---

## 📖 **Paso a Paso: Agregar Minecraft Bedrock 1.26.x**

### **Paso 1: Investigar el Protocol ID de la nueva versión**

Primero necesitas saber el **número de protocolo** de Minecraft 1.26.x. Esto lo puedes obtener de:

1. **Repositorios oficiales de PocketMine:**
   - https://github.com/pmmp/BedrockProtocol
   - Busca en releases/tags la versión 1.26.x

2. **Wiki de Protocol:**
   - https://wiki.vg/Bedrock_Protocol

3. **Comunidad PMMP:**
   - Discord: https://dsc.gg/pmmpdevs-es

**Ejemplo:** Supongamos que Minecraft 1.26.0 usa el **Protocol 900** (ficticio).

---

### **Paso 2: Editar ProtocolInfo.php**

Abre el archivo `/src/pocketmine/network/mcpe/protocol/ProtocolInfo.php` y realiza estos cambios:

#### **2.1 Actualizar CURRENT_PROTOCOL (línea 39):**

```php
// ANTES:
public const CURRENT_PROTOCOL = self::PROTOCOL_859;

// DESPUÉS:
public const CURRENT_PROTOCOL = self::PROTOCOL_900;
```

#### **2.2 Actualizar versiones mostradas (líneas 42 y 45):**

```php
// ANTES:
public const MINECRAFT_VERSION = "1.21.120";
public const MINECRAFT_VERSION_NETWORK = "1.21.120";

// DESPUÉS:
public const MINECRAFT_VERSION = "1.26.0";
public const MINECRAFT_VERSION_NETWORK = "1.26.0";
```

#### **2.3 Agregar el protocol al array ACCEPTED_PROTOCOLS (línea 47-117):**

Añade la nueva entrada **al final** del array (antes del corchete de cierre):

```php
public const ACCEPTED_PROTOCOLS = [
    ProtocolInfo::PROTOCOL_110,
    // ... todos los demás ...
    ProtocolInfo::PROTOCOL_844,
    ProtocolInfo::PROTOCOL_859,
    ProtocolInfo::PROTOCOL_900  // ← AGREGAR ESTA LÍNEA
];
```

#### **2.4 Definir la constante del protocol (después de línea 224):**

Al final de las definiciones de protocolo (después de PROTOCOL_859), agrega:

```php
//Bedrock Edition 1.26
public const PROTOCOL_900 = 900; // 1.26.0
```

---

### **Paso 3: Agregar soporte para sub-versiones (opcional)**

Si hay **múltiples versiones dentro de 1.26.x** (1.26.0, 1.26.1, 1.26.2), agrégalas también:

```php
//Bedrock Edition 1.26
public const PROTOCOL_900 = 900; // 1.26.0.24, 1.26.0.25, 1.26.0
public const PROTOCOL_910 = 910; // 1.26.10, 1.26.11
public const PROTOCOL_920 = 920; // 1.26.20, 1.26.21
```

Y añade **todas** al array `ACCEPTED_PROTOCOLS`:

```php
ProtocolInfo::PROTOCOL_900,
ProtocolInfo::PROTOCOL_910,
ProtocolInfo::PROTOCOL_920
```

---

### **Paso 4: Actualizar dependencias de protocolo (vendor/)**

Si usas **bedrock-protocol** o **bedrock-data** del vendor, necesitas:

1. **Actualizar composer.json** (si existe):
   ```bash
   composer update pocketmine/bedrock-protocol
   composer update pocketmine/bedrock-data
   ```

2. **Copiar archivos manualmente** si no tienes composer:
   - Descarga las **definiciones de bloques/items** de 1.26 desde:
     - https://github.com/pmmp/BedrockData
   - Colócalas en `/vendor/pocketmine/bedrock-block-upgrade-schema/`

---

### **Paso 5: Verificar compatibilidad de paquetes**

Revisa estos archivos para asegurar compatibilidad:

#### **StartGamePacket.php** (`/src/pocketmine/network/mcpe/protocol/StartGamePacket.php`)

Ya usa `ProtocolInfo::MINECRAFT_VERSION_NETWORK`, así que **se actualizará automáticamente**.

#### **ResourcePackStackPacket.php**

También usa `ProtocolInfo::MINECRAFT_VERSION_NETWORK`. ✅

#### **NetworkBinaryStream.php**

Revisa si hay **nuevos cambios de estructura** en 1.26:
- Compara con https://github.com/pmmp/BedrockProtocol/releases

Si 1.26 cambió estructuras (como pasó con 1.21.60 y StructureEditorData en protocol 776), necesitarás agregar:

```php
if ($protocolVersion >= ProtocolInfo::PROTOCOL_900) {
    // Lógica específica para 1.26+
}
```

---

### **Paso 6: Probar el servidor**

1. **Guarda todos los cambios**
2. **Reinicia el servidor**
3. **Conéctate con Minecraft 1.26.0**
4. **Revisa la consola** para errores:
   ```
   [Server thread/INFO]: Player connected with protocol 900
   ```

---

## ⚙️ **Ejemplo Completo de Edición**

### **Archivo:** `/src/pocketmine/network/mcpe/protocol/ProtocolInfo.php`

```php
/** Actual Minecraft: PE protocol version */
public const CURRENT_PROTOCOL = self::PROTOCOL_900; // ← Cambiado de PROTOCOL_859

/** Current Minecraft PE version reported by the server. */
public const MINECRAFT_VERSION = "1.26.0"; // ← Cambiado de 1.21.120
public const MINECRAFT_VERSION_NETWORK = "1.26.0"; // ← Cambiado de 1.21.120

public const ACCEPTED_PROTOCOLS = [
    ProtocolInfo::PROTOCOL_110,
    ProtocolInfo::PROTOCOL_111,
    // ... todos los demás ...
    ProtocolInfo::PROTOCOL_844,
    ProtocolInfo::PROTOCOL_859,
    ProtocolInfo::PROTOCOL_900  // ← NUEVO
];

// ... más abajo ...

//Bedrock Edition 1.21
public const PROTOCOL_859 = 859; // 1.21.120.24, 1.21.120.25, 1.21.120

//Bedrock Edition 1.26
public const PROTOCOL_900 = 900; // 1.26.0  ← NUEVO
```

---

## 🔧 **Troubleshooting**

### **Error: "Outdated client!"**
- El `CURRENT_PROTOCOL` no está actualizado a 900
- Falta agregar `PROTOCOL_900` al array `ACCEPTED_PROTOCOLS`

### **Error: "Outdated server!"**
- El protocol 900 está bien definido pero falta actualizar `MINECRAFT_VERSION_NETWORK`

### **Error: "Invalid block data" o "Unknown item"**
- Necesitas actualizar `vendor/pocketmine/bedrock-block-upgrade-schema/`
- Descarga los archivos de: https://github.com/pmmp/BedrockData

### **El servidor crashea al iniciar**
- Verifica que **todas las constantes estén bien escritas**
- No agregues comas de más en el array `ACCEPTED_PROTOCOLS`

---

## 📋 **Checklist Final**

- [ ] Investigaste el **protocol ID** correcto de la versión 1.26.x
- [ ] Actualizaste `CURRENT_PROTOCOL` a la nueva versión
- [ ] Actualizaste `MINECRAFT_VERSION` y `MINECRAFT_VERSION_NETWORK`
- [ ] Agregaste `PROTOCOL_900` al array `ACCEPTED_PROTOCOLS`
- [ ] Definiste la constante `public const PROTOCOL_900 = 900;`
- [ ] (Opcional) Actualizaste dependencias de bedrock-protocol
- [ ] Probaste la conexión con Minecraft 1.26.0
- [ ] Revisaste la consola para confirmar que acepta el protocol

---

## 🌟 **Recursos Útiles**

- **Bedrock Protocol Docs:** https://wiki.vg/Bedrock_Protocol
- **BedrockProtocol GitHub:** https://github.com/pmmp/BedrockProtocol
- **BedrockData GitHub:** https://github.com/pmmp/BedrockData
- **Discord PMMP Español:** https://dsc.gg/pmmpdevs-es

---

## 🚀 **Ejemplo Real: Agregando Minecraft 1.21.130 (Hipotético)**

Supongamos que Minecraft lanza la versión **1.21.130** con **protocol 870**:

1. **Editar línea 39:**
   ```php
   public const CURRENT_PROTOCOL = self::PROTOCOL_870;
   ```

2. **Editar líneas 42-45:**
   ```php
   public const MINECRAFT_VERSION = "1.21.130";
   public const MINECRAFT_VERSION_NETWORK = "1.21.130";
   ```

3. **Agregar al array (después de línea 116):**
   ```php
   ProtocolInfo::PROTOCOL_859,
   ProtocolInfo::PROTOCOL_870  // NUEVO
   ```

4. **Definir constante (después de línea 224):**
   ```php
   public const PROTOCOL_859 = 859; // 1.21.120
   public const PROTOCOL_870 = 870; // 1.21.130  // NUEVO
   ```

5. **Guardar, reiniciar, conectar** ✅

---

## 📝 **Notas Adicionales**

- **Compatibilidad hacia atrás:** SubmarineMultiCore mantiene TODOS los protocolos antiguos en `ACCEPTED_PROTOCOLS`, permitiendo que jugadores con versiones antiguas sigan conectándose.
- **Performance:** No hay penalización de rendimiento por soportar múltiples versiones. El servidor identifica el protocol del cliente en el handshake.
- **Plugins:** Verifica que tus plugins sean compatibles con las nuevas versiones de Minecraft.
- **Worlds:** Los mundos son compatibles entre versiones menores (1.21.x → 1.26.x), pero podrían necesitar conversión para cambios mayores.

---

**Documentación creada por Claude Code**