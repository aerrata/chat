<template>
  <div class="col-md-8 space-y">
    <div class="avatar-list">
      <span class="avatar avatar-sm rounded-circle" v-for="user in users" :key="user.id" :style="{ backgroundImage: `url(${user.avatar_url})` }">
        <span :class="[{ 'bg-blue': user.typing, 'bg-success': !user.typing }, 'badge']"></span>
      </span>
    </div>
    <div class="card">
      <div class="row g-0">
        <div class="col-12 col-lg-12 col-xl-12 d-flex flex-column">
          <div class="card-body scrollable" style="height: 60vh" ref="container">
            <div class="chat">
              <div class="chat-bubbles">
                <div class="chat-item" v-for="message in messages" :key="message.id">
                  <div :class="[{ 'justify-content-end': isCurrentUser(message.user) }, 'row align-items-end']">
                    <div class="col-auto" v-if="!isCurrentUser(message.user)">
                      <span class="avatar avatar-xs rounded-circle" :style="{ backgroundImage: `url(${message.user.avatar_url})` }"></span>
                    </div>
                    <div class="col col-auto">
                      <div :class="[{ 'chat-bubble-me': isCurrentUser(message.user) }, 'chat-bubble']">
                        <div class="chat-bubble-title">
                          <div class="row">
                            <div class="col chat-bubble-author">{{ message.user.name }}</div>
                            <div class="col-auto chat-bubble-date">{{ format(new Date(), 'kk:mm') }}</div>
                          </div>
                        </div>
                        <div class="chat-bubble-body">
                          <p>{{ message.content }}</p>
                        </div>
                      </div>
                    </div>
                    <div class="col-auto" v-if="isCurrentUser(message.user)">
                      <span class="avatar avatar-xs rounded-circle" :style="{ backgroundImage: `url(${message.user.avatar_url})` }"></span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card-body small px-4 py-1" v-if="typingUser">
            <p class="text-secondary text-italic">{{ `${typingUser.name} is typing` }}<span class="animated-dots"></span></p>
          </div>

          <div class="card-footer p-2">
            <div class="input-group input-group-flat">
              <input type="text" v-model="message" @keyup.enter="storeMessage" @keyup="sendTypingEvent" class="form-control" placeholder="Type a message" />
              <span class="input-group-text">
                <a href="#" @click.prevent="storeMessage" class="link-secondary text-decoration-none">
                  <i class="ti ti-send icon"></i>
                </a>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { format } from 'date-fns'
import axios from 'axios'
import useToast from '@/composables/useToast'

const { showToast } = useToast()

const props = defineProps({ auth: Object })

const message = ref('')
const messages = ref([])
const users = ref([])
const container = ref(null)

const typingUser = computed(() => {
  return users.value.find((user) => user.typing)
})

const fetchMessages = () => {
  axios.get('/messages').then((response) => {
    messages.value = response.data
    scrollToBottom()
  })
}

const sendTypingEvent = () => {
  Echo.join('chat').whisper('typing', {
    user: props.auth,
  })
}

const storeMessage = () => {
  if (!message.value.trim()) return
  axios.post('/messages', { content: message.value }).then((response) => {
    message.value = ''
    messages.value.push(response.data.data.message)
    scrollToBottom()
  })
}

const isCurrentUser = (user) => user.id === props.auth.id

const scrollToBottom = () => {
  nextTick(() => {
    container.value.scrollTop = container.value.scrollHeight
  })
}

onMounted(() => {
  fetchMessages()

  Echo.join('chat')
    .here((data) => {
      users.value = data
    })
    .joining((item) => {
      users.value.push(item)
      showToast({ content: `${item.name} joined the chat! 🎉` })
    })
    .leaving((item) => {
      users.value = users.value.filter((user) => user.id !== item.id)
    })
    .listenForWhisper('typing', (event) => {
      users.value.forEach((user, index) => {
        if (user.id === event.user.id) {
          user.typing = true
          users.value[index] = user
        }
      })
    })
    .listen('NewMessage', (event) => {
      messages.value.push(event.message)

      users.value.forEach((user, index) => {
        if (user.id === event.message.user.id) {
          user.typing = false
          users.value[index] = user
        }
      })

      scrollToBottom()
    })
})
</script>

<style>
.chat-bubbles {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.chat-bubble {
  background: var(--tblr-bg-surface-secondary);
  border-radius: var(--tblr-border-radius-lg);
  padding: 0.5rem;
  position: relative;
  font-size: 85%;
}

.chat-bubble-me {
  background-color: var(--tblr-primary-lt);
  box-shadow: none;
}

.chat-bubble-title {
  margin-bottom: 0.25rem;
}

.chat-bubble-author {
  font-weight: 600;
}

.chat-bubble-date {
  color: var(--tblr-secondary);
  font-size: 80%;
}

.chat-bubble-body > :last-child {
  margin-bottom: 0;
}

.toast-container > :not(:last-child) {
  margin-bottom: 0.8rem;
}
</style>
