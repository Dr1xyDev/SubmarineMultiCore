# 📚 Documentation: How to Add More Versions to SubmarineMultiCore

## 🎯 Introduction

**SubmarineMultiCore** currently supports versions **1.1.0 → 1.21.120** (protocols 110 → 859). This multiversion system allows players using different Minecraft Bedrock versions to connect to the same server.

---

## 🔍 How the Multiversion System Works

The system uses Minecraft Bedrock **protocol numbers**. Every game version has its own **unique protocol ID**:

- **Example:** Minecraft 1.21.120 = Protocol 859
- **Example:** Minecraft 1.21.110 = Protocol 844

The main file responsible for handling this is:

```txt
/src/pocketmine/network/mcpe/protocol/ProtocolInfo.php
```

---

## 📖 Step-by-Step: Adding Minecraft Bedrock 1.26.x

### Step 1: Find the Protocol ID of the New Version

First, you need to know the **protocol number** used by Minecraft 1.26.x. You can find it from:

1. **Official PocketMine repositories:**
   - https://github.com/pmmp/BedrockProtocol
   - Check the releases/tags for version 1.26.x

2. **Protocol Wiki:**
   - https://wiki.vg/Bedrock_Protocol

3. **PMMP Community:**
   - Discord: https://dsc.gg/pmmpdevs-es

**Example:** Let's assume Minecraft 1.26.0 uses **Protocol 900** (fictional example).

---

### Step 2: Edit ProtocolInfo.php

Open the file `/src/pocketmine/network/mcpe/protocol/ProtocolInfo.php` and make the following changes:

#### 2.1 Update CURRENT_PROTOCOL (line 39)

```php
// BEFORE:
public const CURRENT_PROTOCOL = self::PROTOCOL_859;

// AFTER:
public const CURRENT_PROTOCOL = self::PROTOCOL_900;
```

#### 2.2 Update displayed versions (lines 42 and 45)

```php
// BEFORE:
public const MINECRAFT_VERSION = "1.21.120";
public const MINECRAFT_VERSION_NETWORK = "1.21.120";

// AFTER:
public const MINECRAFT_VERSION = "1.26.0";
public const MINECRAFT_VERSION_NETWORK = "1.26.0";
```

#### 2.3 Add the protocol to the ACCEPTED_PROTOCOLS array (lines 47-117)

Add the new entry **at the end** of the array (before the closing bracket):

```php
public const ACCEPTED_PROTOCOLS = [
    ProtocolInfo::PROTOCOL_110,
    // ... all other protocols ...
    ProtocolInfo::PROTOCOL_844,
    ProtocolInfo::PROTOCOL_859,
    ProtocolInfo::PROTOCOL_900  // ← ADD THIS LINE
];
```

#### 2.4 Define the protocol constant (after line 224)

At the end of the protocol definitions (after `PROTOCOL_859`), add:

```php
//Bedrock Edition 1.26
public const PROTOCOL_900 = 900; // 1.26.0
```

---

### Step 3: Add Support for Sub-Versions (Optional)

If there are **multiple versions inside 1.26.x** (1.26.0, 1.26.1, 1.26.2), add them as well:

```php
//Bedrock Edition 1.26
public const PROTOCOL_900 = 900; // 1.26.0.24, 1.26.0.25, 1.26.0
public const PROTOCOL_910 = 910; // 1.26.10, 1.26.11
public const PROTOCOL_920 = 920; // 1.26.20, 1.26.21
```

And add **all of them** to the `ACCEPTED_PROTOCOLS` array:

```php
ProtocolInfo::PROTOCOL_900,
ProtocolInfo::PROTOCOL_910,
ProtocolInfo::PROTOCOL_920
```

---

### Step 4: Update Protocol Dependencies (vendor/)

If you use **bedrock-protocol** or **bedrock-data** from the vendor directory, you need to:

1. **Update composer.json** (if available):

```bash
composer update pocketmine/bedrock-protocol
composer update pocketmine/bedrock-data
```

2. **Copy files manually** if you don't use composer:
   - Download the **1.26 block/item definitions** from:
     - https://github.com/pmmp/BedrockData
   - Place them inside:
   
```txt
/vendor/pocketmine/bedrock-block-upgrade-schema/
```

---

### Step 5: Verify Packet Compatibility

Check these files to ensure compatibility:

#### StartGamePacket.php

File path:

```txt
/src/pocketmine/network/mcpe/protocol/StartGamePacket.php
```

It already uses `ProtocolInfo::MINECRAFT_VERSION_NETWORK`, so it will **update automatically**.

#### ResourcePackStackPacket.php

Also uses `ProtocolInfo::MINECRAFT_VERSION_NETWORK`. ✅

#### NetworkBinaryStream.php

Check for **new structure changes** introduced in 1.26:

- Compare with:
  - https://github.com/pmmp/BedrockProtocol/releases

If 1.26 changed packet structures (similar to what happened with 1.21.60 and `StructureEditorData` in protocol 776), you will need to add something like:

```php
if ($protocolVersion >= ProtocolInfo::PROTOCOL_900) {
    // Logic specific to 1.26+
}
```

---

### Step 6: Test the Server

1. **Save all changes**
2. **Restart the server**
3. **Connect using Minecraft 1.26.0**
4. **Check the console** for errors:

```txt
[Server thread/INFO]: Player connected with protocol 900
```

---

## ⚙️ Full Editing Example

### File: `/src/pocketmine/network/mcpe/protocol/ProtocolInfo.php`

```php
/** Actual Minecraft: PE protocol version */
public const CURRENT_PROTOCOL = self::PROTOCOL_900; // ← Changed from PROTOCOL_859

/** Current Minecraft PE version reported by the server. */
public const MINECRAFT_VERSION = "1.26.0"; // ← Changed from 1.21.120
public const MINECRAFT_VERSION_NETWORK = "1.26.0"; // ← Changed from 1.21.120

public const ACCEPTED_PROTOCOLS = [
    ProtocolInfo::PROTOCOL_110,
    ProtocolInfo::PROTOCOL_111,
    // ... all other protocols ...
    ProtocolInfo::PROTOCOL_844,
    ProtocolInfo::PROTOCOL_859,
    ProtocolInfo::PROTOCOL_900  // ← NEW
];

// ... lower in the file ...

//Bedrock Edition 1.21
public const PROTOCOL_859 = 859; // 1.21.120.24, 1.21.120.25, 1.21.120

//Bedrock Edition 1.26
public const PROTOCOL_900 = 900; // 1.26.0  ← NEW
```

---

## 🔧 Troubleshooting

### Error: "Outdated client!"

- `CURRENT_PROTOCOL` was not updated to 900
- `PROTOCOL_900` is missing from the `ACCEPTED_PROTOCOLS` array

### Error: "Outdated server!"

- Protocol 900 is properly defined, but `MINECRAFT_VERSION_NETWORK` was not updated

### Error: "Invalid block data" or "Unknown item"

- You need to update:
  
```txt
/vendor/pocketmine/bedrock-block-upgrade-schema/
```

- Download the required files from:
  - https://github.com/pmmp/BedrockData

### Server crashes on startup

- Verify that **all constants are written correctly**
- Make sure there are no extra commas inside the `ACCEPTED_PROTOCOLS` array

---

## 📋 Final Checklist

- [ ] You found the correct **protocol ID** for version 1.26.x
- [ ] You updated `CURRENT_PROTOCOL` to the new version
- [ ] You updated `MINECRAFT_VERSION` and `MINECRAFT_VERSION_NETWORK`
- [ ] You added `PROTOCOL_900` to the `ACCEPTED_PROTOCOLS` array
- [ ] You defined the constant `public const PROTOCOL_900 = 900;`
- [ ] (Optional) You updated the bedrock-protocol dependencies
- [ ] You tested the connection using Minecraft 1.26.0
- [ ] You checked the console to confirm the protocol is accepted

---

## 🌟 Useful Resources

- **Bedrock Protocol Docs:** https://wiki.vg/Bedrock_Protocol
- **BedrockProtocol GitHub:** https://github.com/pmmp/BedrockProtocol
- **BedrockData GitHub:** https://github.com/pmmp/BedrockData
- **PMMP Spanish Discord:** https://dsc.gg/pmmpdevs-es

---

## 🚀 Real Example: Adding Minecraft 1.21.130 (Hypothetical)

Let's assume Minecraft releases version **1.21.130** using **protocol 870**:

1. **Edit line 39:**

```php
public const CURRENT_PROTOCOL = self::PROTOCOL_870;
```

2. **Edit lines 42-45:**

```php
public const MINECRAFT_VERSION = "1.21.130";
public const MINECRAFT_VERSION_NETWORK = "1.21.130";
```

3. **Add to the array (after line 116):**

```php
ProtocolInfo::PROTOCOL_859,
ProtocolInfo::PROTOCOL_870  // NEW
```

4. **Define the constant (after line 224):**

```php
public const PROTOCOL_859 = 859; // 1.21.120
public const PROTOCOL_870 = 870; // 1.21.130  // NEW
```

5. **Save, restart, connect** ✅

---

## 📝 Additional Notes

- **Backward compatibility:** SubmarineMultiCore keeps ALL old protocols inside `ACCEPTED_PROTOCOLS`, allowing players using older versions to continue connecting.
- **Performance:** There is no performance penalty for supporting multiple versions. The server identifies the client's protocol during the handshake process.
- **Plugins:** Make sure your plugins are compatible with newer Minecraft versions.
- **Worlds:** Worlds are generally compatible between minor versions (1.21.x → 1.26.x), but major changes may require world conversion.

---

**Documentation created by Claude Code**