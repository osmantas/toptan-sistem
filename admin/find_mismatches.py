
import sys

def find_mismatches(filename):
    with open(filename, 'r', encoding='utf-8') as f:
        content = f.read()

    stack = []
    for i, char in enumerate(content):
        if char == '{':
            stack.append(('{', i))
        elif char == '}':
            if not stack:
                print(f"Extra '}}' at offset {i}")
            else:
                stack.pop()
        elif char == '(':
            stack.append(('(', i))
        elif char == ')':
            if not stack or stack[-1][0] != '(':
                # We only track ( ) vs { } but they can be nested curiously in JS
                # But for now let's focus on braces
                pass
            else:
                stack.pop()
    
    for char, pos in stack:
        # Get line number
        line_no = content[:pos].count('\n') + 1
        print(f"Unclosed '{char}' at line {line_no}")

if __name__ == "__main__":
    find_mismatches(sys.argv[1])
