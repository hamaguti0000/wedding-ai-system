// ajax.js（普通のスクリプト用）
async function sendForm(url, data) {
  try {
    const response = await fetch(url, {
      method: "POST",
      body: data,
    });
    return await response.json();
  } catch (error) {
    console.error("通信エラー:", error);
    throw error;
  }
}

window.sendForm = sendForm; // グローバルで使えるようにする
