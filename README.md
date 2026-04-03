<p align="center">
    <img src="/public/images/logo.png" width="120" alt="Logo">
</p>

<h1 align="center">🎌 Anime Tracker</h1>

<p align="center">
Sistema completo para organizar, acompanhar e avaliar animes 📺
</p>

---

## 🚀 Sobre o projeto

O **Anime Tracker** é um sistema web desenvolvido em Laravel que permite gerenciar o consumo de animes de forma inteligente, organizada e personalizada.

A ideia do projeto é simular uma experiência parecida com plataformas como MyAnimeList, porém com foco em:

- organização por calendário 📅  
- controle de progresso 📊  
- planejamento semanal 🎯  
- avaliações personalizadas ⭐  

---

## ✨ Funcionalidades

### 📅 Calendário inteligente
- Gera automaticamente um calendário de episódios
- Baseado em:
  - dias selecionados
  - episódios por dia
  - progresso atual
- Suporte a:
  - episódios fixos
  - episódios variáveis ("a definir")

---

### 🎬 Gerenciamento de animes
- Buscar animes via API (Jikan / MyAnimeList)
- Adicionar ao sistema
- Definir:
  - episódio atual
  - dias de exibição
  - ritmo de episódios

---

### 🧠 Sistema de progresso automático
- Calcula automaticamente os próximos episódios
- Atualiza conforme:
  - dias passam
  - logs de episódios são registrados

---

### 🛠️ Painel administrativo
- Editar planos de anime
- Registrar episódios assistidos
- Marcar como:
  - ⏸ Pausado
  - ✔ Concluído
- Sistema automático que:
  - detecta quando o anime terminou

---

### ⭐ Sistema de reviews
- Cada usuário pode:
  - dar nota (1 a 10)
  - escrever comentário
- Exibição:
  - média geral por anime
  - reviews dos usuários

---

### 📊 Organização por status
- 🟢 Em andamento  
- 🟡 Pausados  
- 🔵 Concluídos  

Com navegação dinâmica no painel.

---

### 📱 Interface responsiva
- Totalmente adaptado para:
  - celular
  - tablet
  - desktop

---

## 🧩 Tecnologias utilizadas

- **Laravel 12**
- **PHP 8**
- **MySQL**
- **Blade**
- **Tailwind CSS**
- **Alpine.js**
- **Vite**

---

## ⚙️ Instalação

```bash
# Clonar projeto
git clone https://github.com/seu-user/seu-repo.git

# Entrar na pasta
cd seu-repo

# Instalar dependências
composer install
npm install

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Rodar migrations
php artisan migrate

# Rodar build do front
npm run build

# Iniciar servidor
php artisan serve