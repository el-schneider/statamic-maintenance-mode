<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import {
  Header,
  Button,
  Panel,
  PanelHeader,
  Heading,
  Card,
  Description,
  CommandPaletteItem,
  Input,
  PublishContainer,
} from "@statamic/cms/ui";

const props = defineProps([
  "title",
  "isActive",
  "secretUrl",
  "hasCollections",
  "blueprint",
  "meta",
  "values",
  "activateUrl",
  "deactivateUrl",
  "storeUrl",
]);

const formValues = ref(props.values);
const formMeta = ref(props.meta);
const publishContainer = ref(null);
const saving = ref(false);

function handleKeydown(e) {
  if ((e.ctrlKey || e.metaKey) && e.key === "s") {
    e.preventDefault();
    if (props.hasCollections) {
      save();
    }
  }
}

onMounted(() => {
  window.addEventListener("keydown", handleKeydown);
});

onUnmounted(() => {
  window.removeEventListener("keydown", handleKeydown);
});

function activate() {
  if (
    !confirm(
      __(
        "Are you sure you want to activate maintenance mode? Visitors will see the maintenance page.",
      ),
    )
  ) {
    return;
  }
  fetch(props.activateUrl, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": Statamic.$config.get("csrfToken"),
    },
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Request failed");
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        window.location.reload();
      }
    })
    .catch((error) => {
      Statamic.$toast.error(__("Something went wrong"));
      console.error(error);
    });
}

function deactivate() {
  fetch(props.deactivateUrl, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": Statamic.$config.get("csrfToken"),
    },
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Request failed");
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        window.location.reload();
      }
    })
    .catch((error) => {
      Statamic.$toast.error(__("Something went wrong"));
      console.error(error);
    });
}

function save() {
  if (saving.value) return;

  saving.value = true;

  fetch(props.storeUrl, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": Statamic.$config.get("csrfToken"),
    },
    body: JSON.stringify(formValues.value),
  })
    .then((response) => {
      if (!response.ok) {
        return response.json().then((data) => {
          throw { response, data };
        });
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        Statamic.$toast.success(__("Settings saved"));
        publishContainer.value?.clearDirtyState();
      }
    })
    .catch((error) => {
      if (error.data?.errors) {
        const firstError = Object.values(error.data.errors)[0];
        Statamic.$toast.error(
          Array.isArray(firstError) ? firstError[0] : firstError,
        );
      } else {
        Statamic.$toast.error(__("Something went wrong"));
        console.error(error);
      }
    })
    .finally(() => {
      saving.value = false;
    });
}

function copySecretUrl() {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(props.secretUrl).then(() => {
      Statamic.$toast.success(__("Copied to clipboard"));
    });
  } else {
    const input = document.createElement("input");
    input.value = props.secretUrl;
    document.body.appendChild(input);
    input.select();
    document.execCommand("copy");
    document.body.removeChild(input);
    Statamic.$toast.success(__("Copied to clipboard"));
  }
}
</script>

<template>
  <Header :title="title">
    <CommandPaletteItem
      v-if="hasCollections"
      category="Actions"
      :text="__('Save Configuration')"
      icon="save"
      :action="save"
      v-slot="{ text }"
    >
      <Button :text="__('Save')" variant="primary" @click="save" />
    </CommandPaletteItem>
  </Header>

  <div class="space-y-6">
    <Panel>
      <PanelHeader>
        <Heading :text="__('Status')" />
      </PanelHeader>
      <Card>
        <div class="flex items-center gap-4">
          <CommandPaletteItem
            v-if="isActive"
            category="Actions"
            :text="__('Deactivate Maintenance Mode')"
            icon="checkmark"
            :action="deactivate"
            prioritize
            v-slot="{ text }"
          >
            <Button
              :text="__('Deactivate')"
              style="background: #16a34a; border-color: #15803d; color: white"
              @click="deactivate"
            />
          </CommandPaletteItem>
          <CommandPaletteItem
            v-else
            category="Actions"
            :text="__('Activate Maintenance Mode')"
            icon="alert"
            :action="activate"
            prioritize
            v-slot="{ text }"
          >
            <Button :text="__('Activate')" variant="danger" @click="activate" />
          </CommandPaletteItem>
          <Description>
            {{ __("Maintenance mode is currently") }}
            <span v-if="isActive" class="font-semibold text-red-600">{{
              __("active")
            }}</span
            ><span v-else class="font-semibold text-green-600">{{
              __("inactive")
            }}</span
            >.
          </Description>
        </div>
      </Card>
    </Panel>

    <Panel v-if="isActive && secretUrl">
      <PanelHeader>
        <Heading :text="__('Bypass URL')" />
      </PanelHeader>
      <Card class="bg-amber-50 dark:bg-amber-950/30">
        <Description class="mb-3">
          {{
            __(
              "Share this URL to grant temporary access during maintenance. Visitors who open this link will receive a cookie that bypasses maintenance mode.",
            )
          }}
        </Description>
        <div class="flex items-center gap-2">
          <Input
            type="text"
            readonly
            :model-value="secretUrl"
            class="flex-1 font-mono text-sm"
          />
          <Button :text="__('Copy')" @click="copySecretUrl" />
        </div>
      </Card>
    </Panel>

    <Panel v-if="hasCollections">
      <PanelHeader>
        <Heading :text="__('Configuration')" />
      </PanelHeader>
      <Card>
        <PublishContainer
          ref="publishContainer"
          v-model="formValues"
          :blueprint="blueprint"
          :meta="formMeta"
          @meta-updated="formMeta = $event"
        />
      </Card>
    </Panel>

    <Card v-else>
      <Description>
        {{
          __(
            "No collections available. Create a collection to select a maintenance page or whitelisted pages.",
          )
        }}
      </Description>
    </Card>
  </div>
</template>
