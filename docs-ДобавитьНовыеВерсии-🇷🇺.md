# 📚 ДОКУМЕНТАЦИЯ: Как Добавить Больше Версий в SubmarineMultiCore

## 🎯 Введение

**SubmarineMultiCore** в настоящее время поддерживает версии **1.1.0 → 1.21.120** (протоколы 110 → 859). Эта мультиверсионная система позволяет игрокам с разными версиями Minecraft Bedrock подключаться к одному и тому же серверу.

---

## 🔍 Как Работает Мультиверсионная Система

Система использует **номера протоколов** Minecraft Bedrock. Каждая версия игры имеет свой **уникальный Protocol ID**:

- **Пример:** Minecraft 1.21.120 = Protocol 859
- **Пример:** Minecraft 1.21.110 = Protocol 844

Основной файл, который управляет этим:

```txt
/src/pocketmine/network/mcpe/protocol/ProtocolInfo.php
```

---

## 📖 Пошагово: Добавление Minecraft Bedrock 1.26.x

### Шаг 1: Найти Protocol ID новой версии

Сначала необходимо узнать **номер протокола** Minecraft 1.26.x. Получить его можно из:

1. **Официальных репозиториев PocketMine:**
   - https://github.com/pmmp/BedrockProtocol
   - Найдите версию 1.26.x в releases/tags

2. **Protocol Wiki:**
   - https://wiki.vg/Bedrock_Protocol

3. **Сообщества PMMP:**
   - Discord: https://dsc.gg/pmmpdevs-es

**Пример:** Предположим, что Minecraft 1.26.0 использует **Protocol 900** (вымышленный пример).

---

### Шаг 2: Редактирование ProtocolInfo.php

Откройте файл `/src/pocketmine/network/mcpe/protocol/ProtocolInfo.php` и внесите следующие изменения:

#### 2.1 Обновить CURRENT_PROTOCOL (строка 39)

```php
// ДО:
public const CURRENT_PROTOCOL = self::PROTOCOL_859;

// ПОСЛЕ:
public const CURRENT_PROTOCOL = self::PROTOCOL_900;
```

#### 2.2 Обновить отображаемые версии (строки 42 и 45)

```php
// ДО:
public const MINECRAFT_VERSION = "1.21.120";
public const MINECRAFT_VERSION_NETWORK = "1.21.120";

// ПОСЛЕ:
public const MINECRAFT_VERSION = "1.26.0";
public const MINECRAFT_VERSION_NETWORK = "1.26.0";
```

#### 2.3 Добавить протокол в массив ACCEPTED_PROTOCOLS (строки 47-117)

Добавьте новую запись **в конец массива** (перед закрывающей скобкой):

```php
public const ACCEPTED_PROTOCOLS = [
    ProtocolInfo::PROTOCOL_110,
    // ... все остальные ...
    ProtocolInfo::PROTOCOL_844,
    ProtocolInfo::PROTOCOL_859,
    ProtocolInfo::PROTOCOL_900  // ← ДОБАВИТЬ ЭТУ СТРОКУ
];
```

#### 2.4 Определить константу протокола (после строки 224)

В конце определений протоколов (после `PROTOCOL_859`) добавьте:

```php
//Bedrock Edition 1.26
public const PROTOCOL_900 = 900; // 1.26.0
```

---

### Шаг 3: Добавить Поддержку Подверсий (необязательно)

Если внутри версии 1.26.x есть **несколько подверсий** (1.26.0, 1.26.1, 1.26.2), добавьте и их:

```php
//Bedrock Edition 1.26
public const PROTOCOL_900 = 900; // 1.26.0.24, 1.26.0.25, 1.26.0
public const PROTOCOL_910 = 910; // 1.26.10, 1.26.11
public const PROTOCOL_920 = 920; // 1.26.20, 1.26.21
```

И добавьте **все** в массив `ACCEPTED_PROTOCOLS`:

```php
ProtocolInfo::PROTOCOL_900,
ProtocolInfo::PROTOCOL_910,
ProtocolInfo::PROTOCOL_920
```

---

### Шаг 4: Обновить Зависимости Протокола (vendor/)

Если вы используете **bedrock-protocol** или **bedrock-data** из vendor, необходимо:

1. **Обновить composer.json** (если он существует):

```bash
composer update pocketmine/bedrock-protocol
composer update pocketmine/bedrock-data
```

2. **Скопировать файлы вручную**, если у вас нет composer:
   - Скачайте **определения блоков/предметов** для 1.26 из:
     - https://github.com/pmmp/BedrockData
   - Поместите их в:

```txt
/vendor/pocketmine/bedrock-block-upgrade-schema/
```

---

### Шаг 5: Проверить Совместимость Пакетов

Проверьте следующие файлы для совместимости:

#### StartGamePacket.php

Путь к файлу:

```txt
/src/pocketmine/network/mcpe/protocol/StartGamePacket.php
```

Он уже использует `ProtocolInfo::MINECRAFT_VERSION_NETWORK`, поэтому обновится **автоматически**.

#### ResourcePackStackPacket.php

Также использует `ProtocolInfo::MINECRAFT_VERSION_NETWORK`. ✅

#### NetworkBinaryStream.php

Проверьте, появились ли **новые изменения структуры** в версии 1.26:

- Сравните с:
  - https://github.com/pmmp/BedrockProtocol/releases

Если в 1.26 изменились структуры (как это было в 1.21.60 с `StructureEditorData` в protocol 776), потребуется добавить:

```php
if ($protocolVersion >= ProtocolInfo::PROTOCOL_900) {
    // Логика специально для 1.26+
}
```

---

### Шаг 6: Протестировать Сервер

1. **Сохраните все изменения**
2. **Перезапустите сервер**
3. **Подключитесь через Minecraft 1.26.0**
4. **Проверьте консоль** на наличие ошибок:

```txt
[Server thread/INFO]: Player connected with protocol 900
```

---

## ⚙️ Полный Пример Редактирования

### Файл: `/src/pocketmine/network/mcpe/protocol/ProtocolInfo.php`

```php
/** Actual Minecraft: PE protocol version */
public const CURRENT_PROTOCOL = self::PROTOCOL_900; // ← Изменено с PROTOCOL_859

/** Current Minecraft PE version reported by the server. */
public const MINECRAFT_VERSION = "1.26.0"; // ← Изменено с 1.21.120
public const MINECRAFT_VERSION_NETWORK = "1.26.0"; // ← Изменено с 1.21.120

public const ACCEPTED_PROTOCOLS = [
    ProtocolInfo::PROTOCOL_110,
    ProtocolInfo::PROTOCOL_111,
    // ... все остальные ...
    ProtocolInfo::PROTOCOL_844,
    ProtocolInfo::PROTOCOL_859,
    ProtocolInfo::PROTOCOL_900  // ← НОВОЕ
];

// ... ниже в файле ...

//Bedrock Edition 1.21
public const PROTOCOL_859 = 859; // 1.21.120.24, 1.21.120.25, 1.21.120

//Bedrock Edition 1.26
public const PROTOCOL_900 = 900; // 1.26.0  ← НОВОЕ
```

---

## 🔧 Устранение Неполадок

### Ошибка: "Outdated client!"

- `CURRENT_PROTOCOL` не обновлён до 900
- `PROTOCOL_900` отсутствует в массиве `ACCEPTED_PROTOCOLS`

### Ошибка: "Outdated server!"

- Протокол 900 определён правильно, но не обновлён `MINECRAFT_VERSION_NETWORK`

### Ошибка: "Invalid block data" или "Unknown item"

- Необходимо обновить:

```txt
/vendor/pocketmine/bedrock-block-upgrade-schema/
```

- Скачайте файлы отсюда:
  - https://github.com/pmmp/BedrockData

### Сервер вылетает при запуске

- Проверьте, что **все константы написаны правильно**
- Не добавляйте лишние запятые в массив `ACCEPTED_PROTOCOLS`

---

## 📋 Финальный Чеклист

- [ ] Вы нашли правильный **protocol ID** для версии 1.26.x
- [ ] Вы обновили `CURRENT_PROTOCOL` до новой версии
- [ ] Вы обновили `MINECRAFT_VERSION` и `MINECRAFT_VERSION_NETWORK`
- [ ] Вы добавили `PROTOCOL_900` в массив `ACCEPTED_PROTOCOLS`
- [ ] Вы определили константу `public const PROTOCOL_900 = 900;`
- [ ] (Необязательно) Вы обновили зависимости bedrock-protocol
- [ ] Вы протестировали подключение через Minecraft 1.26.0
- [ ] Вы проверили консоль и убедились, что протокол принимается

---

## 🌟 Полезные Ресурсы

- **Документация Bedrock Protocol:** https://wiki.vg/Bedrock_Protocol
- **GitHub BedrockProtocol:** https://github.com/pmmp/BedrockProtocol
- **GitHub BedrockData:** https://github.com/pmmp/BedrockData
- **Discord PMMP Español:** https://dsc.gg/pmmpdevs-es

---

## 🚀 Реальный Пример: Добавление Minecraft 1.21.130 (Гипотетически)

Предположим, что Minecraft выпускает версию **1.21.130** с **protocol 870**:

1. **Изменить строку 39:**

```php
public const CURRENT_PROTOCOL = self::PROTOCOL_870;
```

2. **Изменить строки 42-45:**

```php
public const MINECRAFT_VERSION = "1.21.130";
public const MINECRAFT_VERSION_NETWORK = "1.21.130";
```

3. **Добавить в массив (после строки 116):**

```php
ProtocolInfo::PROTOCOL_859,
ProtocolInfo::PROTOCOL_870  // НОВОЕ
```

4. **Определить константу (после строки 224):**

```php
public const PROTOCOL_859 = 859; // 1.21.120
public const PROTOCOL_870 = 870; // 1.21.130  // НОВОЕ
```

5. **Сохранить, перезапустить, подключиться** ✅

---

## 📝 Дополнительные Примечания

- **Обратная совместимость:** SubmarineMultiCore сохраняет ВСЕ старые протоколы в `ACCEPTED_PROTOCOLS`, позволяя игрокам со старыми версиями продолжать подключаться.
- **Производительность:** Поддержка нескольких версий не влияет на производительность. Сервер определяет протокол клиента во время handshake.
- **Плагины:** Убедитесь, что ваши плагины совместимы с новыми версиями Minecraft.
- **Миры:** Миры обычно совместимы между минорными версиями (1.21.x → 1.26.x), однако для крупных изменений может потребоваться конвертация мира.

---

**Документация создана Claude Code**