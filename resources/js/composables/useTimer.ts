import { computed, onUnmounted, ref } from 'vue';

export function useTimer() {
    const remainingTime = ref(0);
    const isRunning = ref(false);
    let intervalId: number | null = null;

    function startTimer(seconds: number, onTimeout: () => void) {
        stopTimer();
        remainingTime.value = seconds;
        isRunning.value = true;

        intervalId = window.setInterval(() => {
            remainingTime.value--;
            if (remainingTime.value <= 0) {
                stopTimer();
                onTimeout();
            }
        }, 1000);
    }

    function stopTimer() {
        if (intervalId !== null) {
            clearInterval(intervalId);
            intervalId = null;
        }
        isRunning.value = false;
    }

    function resetTimer(seconds: number) {
        remainingTime.value = seconds;
    }

    const formattedTime = computed(() => {
        const minutes = Math.floor(remainingTime.value / 60);
        const seconds = remainingTime.value % 60;
        return `${minutes}:${seconds.toString().padStart(2, '0')}`;
    });

    onUnmounted(() => {
        stopTimer();
    });

    return {
        remainingTime,
        isRunning,
        formattedTime,
        startTimer,
        stopTimer,
        resetTimer,
    };
}
